---
name: github-issue-creator
description: Convert raw notes, error logs, voice dictation, or screenshots into crisp GitHub-flavored markdown issue reports. Use when the user pastes bug info, error messages, or informal descriptions and wants a structured GitHub issue. Supports images/GIFs for visual evidence.
---

# GitHub Issue Creator (Ganesha Smart Edutourism Edition)

Transform messy input (error logs, voice notes, screenshots) into clean, actionable GitHub issues tailored for the Ganesha Smart Edutourism project.

## Output Template

```markdown
## Summary

[One-line description of the issue]

## Environment

- **Device/OS**: [e.g., iPhone 15 / iOS 17, Desktop / Ubuntu 24.04]
- **Browser**: [e.g., Safari Mobile, Chrome 124]
- **App Environment**: [Local / Production]

## Reproduction Steps

1. [Step]
2. [Step]
3. [Step]

## Expected Behavior

[What should happen]

## Actual Behavior

[What actually happens]

## Error Details
```

[Error message/code if applicable]

```

## Visual Evidence
[Reference to attached screenshots/GIFs, e.g. ![AR View Camera Fail](screenshot.png)]

## Impact
[Severity: Critical/High/Medium/Low + brief explanation]

## Additional Context
[Any other relevant details, such as API response payloads or file path references]
```

## Output Location

**Create issues as markdown files** in `/issues/` directory at the repo root. Use naming convention: `YYYY-MM-DD-short-description.md`

## Guidelines

**Be crisp**: No fluff. Every word should add value.

**Extract structure from chaos**: Voice dictation and raw notes often contain the facts buried in casual language. Pull them out.

**Project Awareness**: Default the environment section to the Ganesha Smart Edutourism stack (Laravel v13, PHP 8.4, TailwindCSS v4, MySQL) unless specified otherwise.

**Placeholder sensitive data**: Use `[USER_ID]`, `[MIDTRANS_SERVER_KEY]`, `[MAPS_API_KEY]`, etc. for anything that might be sensitive.

**Match severity to impact**:

- Critical: Application crash, booking flow or payment gateway down, data loss, security issue
- High: Major feature broken (e.g. AR Camera totally black screen), no workaround
- Medium: Feature impaired (e.g. Bento grid alignment issues on certain screens), workaround exists
- Low: Minor inconvenience, typos, cosmetic UI issues

**Image/GIF handling**: Reference attachments inline. Format: `![Description](attachment-name.png)`

## Examples

**Input (voice dictation about AR Feature)**:

> so I was testing the AR Camera feature on my Android phone using Chrome, and when I clicked the floating AR Camera button in the navbar, the screen just turned completely black. I did get a prompt to allow camera permissions, and I allowed it, but still nothing but a black screen and no error message popped up in the HUD.

**Output**:

```markdown
## Summary

AR Camera displays black screen on Android Chrome after granting permission

## Environment

- **Device/OS**: Google Pixel 7 / Android 14
- **Browser**: Google Chrome 125
- **App Environment**: Local

## Reproduction Steps

1. Open the web app on Android Chrome.
2. Tap the floating "AR Camera" button in the middle of the bottom navigation bar.
3. Grant camera permission when prompted.
4. Observe the HUD loaded but the camera feed is completely black.

## Expected Behavior

Camera feed should render behind the glassmorphism HUD overlay, allowing interaction with AR markers.

## Actual Behavior

The screen turns black inside the HUD area, and no feed from the device's camera is displayed. No error message is shown in the UI.

## Impact

**High** - The AR camera is a signature feature of the app and is currently non-functional for Android Chrome users.

## Additional Context

Device tested: Google Pixel 7, Android 14, Chrome version 125. Camera permission was successfully saved in browser settings.
```

---

**Input (error paste about Ticket Scan duplicate detection)**:

> Gate staff report that scanning a ticket they just recorded a few seconds earlier immediately shows the "Tiket Sudah Dipakai" (duplicate) screen, even though it's the first real scan of that ticket.
> No error is logged server-side, but `duplicate_attempts` on the row is incrementing for tickets that were only scanned once by a visitor.

**Output**:

```markdown
## Summary

Ticket scanner flags a freshly recorded ticket as a duplicate on the next scan

## Environment

- **Device/OS**: Staff Android phone, Chrome
- **Browser**: Google Chrome (mobile)
- **App Environment**: Local / Production

## Reproduction Steps

1. Open `/staff/ticketing` and scan a ticket QR code.
2. Fill in the quick form and tap "Simpan" to record the visit.
3. Tap "Scan Tiket Berikutnya" while still holding the phone over the same QR code.
4. Observe the duplicate result panel appears for the ticket just saved.

## Expected Behavior

The camera should not re-read the same code until a new ticket is presented; recording a visit should not immediately flag it as a duplicate.

## Actual Behavior

The camera keeps scanning in the background while the result panel is shown, so the just-saved code is read again and `TicketScanController::check`/`store` increments `duplicate_attempts` on a ticket that was only scanned once.

## Impact

**High** - Corrupts the `duplicate_attempts` metric that exists to detect ticket fraud, and confuses gate staff.

## Additional Context

Check whether the camera (`html5-qrcode`) is paused while the form or result panel is visible, and resumed only when the officer taps "Scan Tiket Berikutnya".
```
