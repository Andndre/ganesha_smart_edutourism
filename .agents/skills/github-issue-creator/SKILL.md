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

**Input (error paste about Midtrans Payment)**:

> Midtrans webhook returning error:
> Signature key verification failed for order_id: ORDER-10294. 
> Error logs show: "Invalid signature hash. Expected: 3c5a6d... Got: 8e2c1a..."

**Output**:

```markdown
## Summary

Midtrans Webhook: Signature key verification failed for orders

## Environment

- **Device/OS**: Midtrans Server Webhook
- **Browser**: N/A
- **App Environment**: Sandbox / Production

## Reproduction Steps

1. Trigger a payment status update from Midtrans Sandbox (e.g. settlement).
2. The Midtrans webhook handler endpoint `/api/payment/webhook` receives the payload.
3. Verify signature hash calculation.

## Expected Behavior

Webhook successfully verifies the SHA512 signature hash generated using `order_id`, `status_code`, `gross_amount`, and the server key, then updates the booking status.

## Actual Behavior

Webhook validation fails with an invalid signature hash error, preventing booking status updates.

## Error Details
```

Signature key verification failed for order_id: ORDER-10294. 
Invalid signature hash. Expected: [EXPECTED_HASH] Got: [RECEIVED_HASH]

```

## Impact

**Critical** - Blocks automated booking status updates when customers complete payments.

## Additional Context

Check if `MIDTRANS_SERVER_KEY` is loaded correctly from `.env` in the webhook controller, and verify if the concatenation order for the SHA512 signature matches Midtrans API specification.
```
