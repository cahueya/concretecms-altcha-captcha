# Altcha CAPTCHA for ConcreteCMS

This package integrates [Altcha](https://altcha.org/) — a privacy-friendly, self-hosted CAPTCHA — into ConcreteCMS (v9+). It works with ConcreteCMS's core CAPTCHA system and is fully self-hosted with no third-party calls.

> ✅ No tracking · No cookies · No remote requests · Fully open-source

---

## 🛠 Installation

To use this package, you must install **two components manually**:

---

### 1. Install the Altcha PHP wrapper

Install the backend verification library via Composer in your ConcreteCMS root directory:

```composer require altcha-org/altcha```

### 2. Include altcha.js manually
The Altcha frontend is not bundled automatically. You must manually copy the JavaScript and CSS into your ConcreteCMS package's asset folder.

Option A: Use npm (recommended)
1. Install Altcha in a temp folder:

```npm install altcha```

2. Copy the files:

```cp node_modules/altcha/dist/altcha.min.js packages/altcha_captcha/assets/js/altcha.js```
```cp node_modules/altcha/dist/altcha.css     packages/altcha_captcha/assets/css/altcha.css```

✅ You may rename altcha.min.js to altcha.js to simplify asset registration.

Option B: Download manually
1. Visit the Altcha GitHub repository:
https://github.com/AltchaOrg/altcha

2. Download from the /dist directory:
- altcha.min.js
- altcha.css

3. Place them into your package:

- packages/altcha_captcha/assets/js/altcha.js
- packages/altcha_captcha/assets/css/altcha.css

The ConcreteCMS package registers these assets on load.

### 3. HMAC Key Setup
Altcha requires a secure 256-bit HMAC key to sign and verify challenges.

How to generate one:
Command line:
```openssl rand -hex 32```

Online generator:
https://codebeautify.org/hmac-generator

Your key must be a 64-character hexadecimal string.