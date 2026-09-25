#!/usr/bin/env node
/**
 * i18n-sync: find missing __() keys, detect orphans, and optionally auto-translate via LibreTranslate (docker).
 *
 * Usage:
 *   node scripts/i18n-sync.mjs                  # dry-run: list missing keys and orphans
 *   node scripts/i18n-sync.mjs --write          # translate missing & write to lang/*.json
 *   node scripts/i18n-sync.mjs --cleanup-orphans # remove unused keys from lang files
 */

import { execSync } from 'child_process';
import { readFileSync, writeFileSync, readdirSync, existsSync } from 'fs';
import { resolve, dirname, join } from 'path';
import { fileURLToPath } from 'url';

const __dir = dirname(fileURLToPath(import.meta.url));
const ROOT = resolve(__dir, '..');
const ID_JSON = resolve(ROOT, 'lang/id.json');
const EN_JSON = resolve(ROOT, 'lang/en.json');
const GLOSSARY_FILE = resolve(ROOT, 'resources/data/i18n-glossary.json');
const SEARCH_DIRS = ['resources', 'app', 'routes'];

function resolveLtUrl() {
  return process.env.LIBRETRANSLATE_URL || 'http://localhost:5000';
}

let LT_URL = resolveLtUrl();

async function isLibreTranslateReady(url) {
  try {
    const res = await fetch(`${url}/languages`, { method: 'GET', signal: AbortSignal.timeout(2000) });
    return res.ok;
  } catch {
    return false;
  }
}

async function ensureLibreTranslate() {
  const url = resolveLtUrl();
  if (await isLibreTranslateReady(url)) {
    return { url, startedContainer: false };
  }

  console.log('⏳ LibreTranslate is not running. Starting container via docker compose...');
  try {
    execSync('docker compose up -d penglipuran-libretranslate', { stdio: 'inherit', cwd: ROOT });
  } catch (err) {
    throw new Error('Gagal menyalakan LibreTranslate. Pastikan Docker Desktop sedang aktif: ' + err.message);
  }

  process.stdout.write('⏳ Waiting for LibreTranslate models to load');
  const maxWaitMs = 60000;
  const start = Date.now();
  while (Date.now() - start < maxWaitMs) {
    if (await isLibreTranslateReady(url)) {
      console.log('\n✓ LibreTranslate is ready!\n');
      return { url, startedContainer: true };
    }
    process.stdout.write('.');
    await new Promise(r => setTimeout(r, 2000));
  }

  throw new Error('\n✗ Timeout (60s) menunggu LibreTranslate siap.');
}

function stopLibreTranslate() {
  console.log('\n⏳ Stopping LibreTranslate container to free system resources...');
  try {
    execSync('docker compose stop penglipuran-libretranslate', { stdio: 'inherit', cwd: ROOT });
    console.log('✓ LibreTranslate stopped successfully.\n');
  } catch (err) {
    console.warn('⚠ Could not stop LibreTranslate automatically:', err.message);
  }
}

const WRITE = process.argv.includes('--write');
const CLEANUP_ORPHANS = process.argv.includes('--cleanup-orphans');

// ── 1. Extract all __() keys from source files ──────────────────────────────

const KEY_RE = /__\(\s*['"]([^'"]+)['"]/g;

function* walkFiles(dir, exts) {
  for (const entry of readdirSync(dir, { withFileTypes: true })) {
    const full = join(dir, entry.name);
    if (entry.isDirectory()) yield* walkFiles(full, exts);
    else if (exts.some(e => entry.name.endsWith(e))) yield full;
  }
}

function extractKeys() {
  const keys = new Set();
  for (const dir of SEARCH_DIRS) {
    for (const file of walkFiles(resolve(ROOT, dir), ['.php', '.blade.php'])) {
      const src = readFileSync(file, 'utf8');
      for (const m of src.matchAll(KEY_RE)) keys.add(m[1]);
    }
  }
  return keys;
}

// ── 2. Load JSON, preserving order ──────────────────────────────────────────

function loadJson(path) {
  return JSON.parse(readFileSync(path, 'utf8'));
}

// ── 3. Translate via LibreTranslate (direct HTTP to container IP) ─────────

const glossaryConfig = existsSync(GLOSSARY_FILE)
  ? loadJson(GLOSSARY_FILE)
  : { protected_terms: [], directional_transforms: {} };

const PROTECTED_TERMS = (glossaryConfig.protected_terms || []).sort((a, b) => b.length - a.length);
const DIRECTIONAL_TRANSFORMS = glossaryConfig.directional_transforms || {};

function glossaryProtect(text, src, tgt) {
  const map = [];
  let out = text;

  const key = `${src}->${tgt}`;
  const rules = DIRECTIONAL_TRANSFORMS[key] || [];
  rules.forEach(({ pattern, flags, replacement }) => {
    const re = new RegExp(pattern, flags);
    out = out.replace(re, (match, ...groups) => {
      let transformed = replacement;
      groups.slice(0, -2).forEach((g, idx) => {
        transformed = transformed.replaceAll(`$${idx + 1}`, g ? g.trim() : '');
      });
      const token = `xgloss${map.length}x`;
      map.push({ token, original: transformed });
      return token;
    });
  });

  PROTECTED_TERMS.forEach(term => {
    const re = new RegExp(term.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'), 'gi');
    out = out.replace(re, match => {
      const token = `xgloss${map.length}x`;
      map.push({ token, original: match });
      return token;
    });
  });

  return { text: out, map };
}

function glossaryRestore(text, map) {
  return map.reduce((s, { token, original }) => s.replaceAll(token, original), text);
}

// Laravel placeholders (:count, :owner, ...) must survive translation untouched.
// Swap them for tokens LibreTranslate won't translate/re-case, then swap back.
const PLACEHOLDER_RE = /:[a-zA-Z_]+/g;

async function translate(text, source, target) {
  const placeholders = text.match(PLACEHOLDER_RE) || [];
  let i = 0;
  let protectedText = text.replace(PLACEHOLDER_RE, () => `xph${i++}x`);

  const { text: textWithGlossary, map: glossaryMap } = glossaryProtect(protectedText, source, target);

  const res = await fetch(`${LT_URL}/translate`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ q: textWithGlossary, source, target, format: 'text' }),
  });
  const data = await res.json();
  if (!data.translatedText) throw new Error(`LibreTranslate error: ${JSON.stringify(data)}`);

  let restored = glossaryRestore(data.translatedText, glossaryMap);
  return restored.replace(/xph(\d+)x/gi, (_, i) => placeholders[Number(i)]);
}

// ── 4. Write sorted JSON ─────────────────────────────────────────────────────

function writeSorted(path, obj) {
  // Plain ordinal sort (not localeCompare) to match existing file ordering,
  // where uppercase letters sort before lowercase (e.g. "AR Scan" before "Acara").
  const sorted = Object.fromEntries(
    Object.entries(obj).sort(([a], [b]) => (a < b ? -1 : a > b ? 1 : 0))
  );
  writeFileSync(path, JSON.stringify(sorted, null, 4) + '\n');
}

// ── Main ─────────────────────────────────────────────────────────────────────

const idMap = loadJson(ID_JSON);
const enMap = loadJson(EN_JSON);

// Union of: keys used in source, keys already in id.json, keys already in en.json.
// Reconciling against existing keys (not just source-extracted ones) catches orphan
// keys that exist in one lang file but not the other (e.g. left behind after a
// blade view was edited without updating both files).
const allKeys = new Set([...extractKeys(), ...Object.keys(idMap), ...Object.keys(enMap)]);

const missingId = [...allKeys].filter(k => !(k in idMap));
const missingEn = [...allKeys].filter(k => !(k in enMap));
const missing = [...new Set([...missingId, ...missingEn])].sort();

// Detect orphan keys: present in lang files but not used in source
const sourceKeys = extractKeys();
const orphans = [...Object.keys(idMap), ...Object.keys(enMap)]
  .filter(k => !sourceKeys.has(k))
  .filter((k, i, arr) => i === arr.indexOf(k))
  .sort();

if (missing.length === 0 && orphans.length === 0) {
  console.log('✓ All __() keys are present in both lang files, and no orphan keys found.');
  process.exit(0);
}

if (missing.length > 0) {
  console.log(`\nFound ${missing.length} missing key(s):\n`);
  for (const key of missing) {
    const inId = key in idMap ? '✓' : '✗';
    const inEn = key in enMap ? '✓' : '✗';
    console.log(`  [id:${inId} en:${inEn}] ${key}`);
  }
}

if (orphans.length > 0) {
  console.log(`\n⚠ Found ${orphans.length} orphan key(s) in lang files but not used in source:\n`);
  for (const key of orphans) {
    console.log(`  • ${key}`);
  }
  console.log(`\nTo remove orphans, use: node scripts/i18n-sync.mjs --cleanup-orphans\n`);
}

if (CLEANUP_ORPHANS) {
  if (orphans.length === 0) {
    console.log('✓ No orphan keys to clean up.\n');
    process.exit(0);
  }

  const sourceKeys = extractKeys();
  let removed = 0;
  for (const key of orphans) {
    if (!(key in sourceKeys)) {
      delete idMap[key];
      delete enMap[key];
      console.log(`  [removed] ${key}`);
      removed++;
    }
  }

  writeSorted(ID_JSON, idMap);
  writeSorted(EN_JSON, enMap);
  console.log(`\n✓ Removed ${removed} orphan key(s). Both lang files updated.\n`);
  process.exit(0);
}

if (!WRITE) {
  console.log(`\nRun with --write to auto-translate and insert into lang files.`);
  console.log(`Run with --cleanup-orphans to remove unused keys from lang files.\n`);
  process.exit(0);
}

if (missing.length === 0) {
  console.log('✓ All keys are already translated. Nothing to write.\n');
  process.exit(0);
}

let startedContainer = false;
try {
  const ready = await ensureLibreTranslate();
  startedContainer = ready.startedContainer;
  LT_URL = ready.url;

  console.log(`\nTranslating ${missing.length} missing key(s) via ${LT_URL}...\n`);

  let added = 0;
  for (const key of missing) {
    try {
      if (!(key in idMap)) {
        idMap[key] = key;
        console.log(`  [id] + "${key}"`);
      }

      if (!(key in enMap)) {
        const translated = await translate(key, 'id', 'en');
        enMap[key] = translated;
        console.log(`  [en] + "${key}" → "${translated}"`);
      }

      added++;
    } catch (err) {
      console.error(`  ✗ Failed "${key}": ${err.message}`);
    }
  }

  writeSorted(ID_JSON, idMap);
  writeSorted(EN_JSON, enMap);

  console.log(`\n✓ Added ${added} key(s). Both lang files updated and sorted.\n`);
} catch (err) {
  console.error(`\n✗ Error: ${err.message}\n`);
  process.exit(1);
} finally {
  if (startedContainer) {
    stopLibreTranslate();
  }
}
