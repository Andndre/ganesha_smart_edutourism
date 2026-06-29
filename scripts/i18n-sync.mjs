#!/usr/bin/env node
/**
 * i18n-sync: find missing __() keys and optionally auto-translate via LibreTranslate (docker).
 *
 * Usage:
 *   node scripts/i18n-sync.mjs          # dry-run: list missing keys
 *   node scripts/i18n-sync.mjs --write  # translate & write to lang/*.json
 */

import { execSync } from 'child_process';
import { readFileSync, writeFileSync, readdirSync, statSync } from 'fs';
import { resolve, dirname, join } from 'path';
import { fileURLToPath } from 'url';

const __dir = dirname(fileURLToPath(import.meta.url));
const ROOT = resolve(__dir, '..');
const ID_JSON = resolve(ROOT, 'lang/id.json');
const EN_JSON = resolve(ROOT, 'lang/en.json');
const SEARCH_DIRS = ['resources', 'app', 'routes'];
// ponytail: resolve container IP dynamically — no curl/wget inside container, but IP is reachable from host
const LT_URL = (() => {
  try {
    const ip = execSync(
      `docker inspect --format '{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}' $(docker ps --filter "name=libretranslate" --format "{{.ID}}" | head -1)`,
      { encoding: 'utf8', shell: '/bin/bash' }
    ).trim();
    return ip ? `http://${ip}:5000` : null;
  } catch { return null; }
})();
const WRITE = process.argv.includes('--write');

// ── 1. Extract all __() keys from source files ──────────────────────────────

const KEY_RE = /__\(['"]([^'"]+)['"]\)/g;

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

async function translate(text, source, target) {
  const res = await fetch(`${LT_URL}/translate`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ q: text, source, target, format: 'text' }),
  });
  const data = await res.json();
  if (!data.translatedText) throw new Error(`LibreTranslate error: ${JSON.stringify(data)}`);
  return data.translatedText;
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

if (missing.length === 0) {
  console.log('✓ All __() keys are present in both lang files.');
  process.exit(0);
}

console.log(`\nFound ${missing.length} missing key(s):\n`);
for (const key of missing) {
  const inId = key in idMap ? '✓' : '✗';
  const inEn = key in enMap ? '✓' : '✗';
  console.log(`  [id:${inId} en:${inEn}] ${key}`);
}

if (!WRITE) {
  console.log(`\nRun with --write to auto-translate and insert into lang files.\n`);
  process.exit(0);
}

if (!LT_URL) {
  console.error(`\n✗ LibreTranslate container not found. Start it with: docker compose up -d libretranslate\n`);
  process.exit(1);
}

console.log(`\nTranslating via ${LT_URL}...\n`);

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
