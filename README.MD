[![Tests](https://github.com/cahueya/concretecms-altcha-captcha/actions/workflows/tests.yml/badge.svg)](https://github.com/cahueya/concretecms-altcha-captcha/actions/workflows/tests.yml)

# ALTCHA CAPTCHA for Concrete CMS

Version 1.1.2 provides a self-hosted, privacy-friendly proof-of-work bot check for Concrete CMS 9.

## Privacy and network behavior

At runtime all CAPTCHA traffic stays on the same Concrete CMS installation:

1. the package's locally bundled browser code runs in the visitor's browser;
2. it requests a fresh challenge from `/altcha-captcha/challenge` on the same origin;
3. PBKDF2/SHA-256 proof-of-work is solved locally by the vendored official ALTCHA PBKDF2 worker;
4. the normal form POST is verified locally by the ALTCHA PHP library.

There are no runtime requests to ALTCHA, Google, hCaptcha, a CDN, an IP reputation service, or another external CAPTCHA service.

## Protection layers in 1.1.2

### ALTCHA PoW v2

The package uses PBKDF2/SHA-256 with an interactive profile of cost 1500 and a random deterministic counter between 500 and 1200. The browser solves the challenge in up to four Web Workers using ALTCHA Widget v3.2.1's official PBKDF2 worker, bundled locally with this package. ALTCHA's key-signature mode is enabled so PHP can verify a valid solution without repeating the expensive PBKDF2 search.

The lighter profile is intentional for invisible form verification: rate limiting, the honeypot and replay protection remain independent defenses, while normal visitors should spend substantially less time waiting for the proof-of-work. The browser also starts preparing the challenge after the visitor first interacts with the protected form, so most of the work happens while a human is naturally filling in fields rather than after pressing Submit.

### Replay protection

Only successfully verified challenge signatures are recorded, and only as SHA-256 hashes. Each row expires with the signed challenge itself. Expired rows are removed opportunistically.

### Local rate limiting

The challenge endpoint allows 20 challenge requests per 10 minutes per client IP. CAPTCHA verification allows 10 attempts per 10 minutes per client IP.

Raw IP addresses are never stored. The database contains only an HMAC-derived client token, a counter and an expiration timestamp. Expired buckets are deleted automatically.

### Honeypot

An off-screen honeypot field adds a no-interaction second signal against generic form-filling bots. Normal visitors and assistive technologies do not interact with it.

## Requirements

- Concrete CMS 9.0+
- PHP 8.1+
- a modern browser with Web Crypto and Web Worker support

## Installation

### Concrete CMS installed with Composer

Require the package from the root directory of your Concrete CMS installation:

```bash
composer require cahueya/altcha_captcha
```

Composer places the package in `packages/altcha_captcha` and installs its PHP dependencies.

### Concrete CMS installed without Composer

Download the ZIP archive of the latest release from the [releases page](https://github.com/cahueya/concretecms-altcha-captcha/releases), and extract it to `packages/altcha_captcha`.

The archive already contains all the required PHP dependencies: there's no need to run Composer.

### Enabling the package

Install or upgrade the package in Concrete CMS and select **ALTCHA CAPTCHA** as the site's CAPTCHA library.

The HMAC secret is generated locally with `random_bytes()` during installation/upgrade. There is no external API key to create or paste.

## Upgrading from 1.0.x

Replace the package files as described above, then run the Concrete CMS package upgrade. The upgrade creates the short-lived rate-limit table automatically. The existing HMAC secret is retained.

Outstanding challenges from an older page load become invalid after the browser receives the 1.1.2 assets; clearing the Concrete CMS cache after upgrade is recommended.

## Local development

The browser solver uses Web Crypto and therefore needs a secure browser context.

`http://localhost`, `http://127.0.0.1`, and `http://*.localhost` are generally treated as trustworthy local origins by modern browsers. A custom hostname such as `http://development.test` is not automatically trusted.

For `development.test`, use one of these development-only options:

- configure local HTTPS (recommended);
- use a `.localhost` alias such as `development.localhost`;
- in Chromium/Chrome, temporarily add `http://development.test` under `chrome://flags/#unsafely-treat-insecure-origin-as-secure` and restart the browser.

Never use the insecure-origin browser exception for production.

## 1.1.0

- migrated from legacy ALTCHA PoW v1/SHA-256 to PoW v2 PBKDF2/SHA-256;
- uses cost 5000 and deterministic counter 5000–10000;
- enabled key signatures for fast server-side verification;
- added self-hosted parallel Web Worker solving with no external browser dependency;
- added a local challenge rate limit: 20 requests / 10 minutes / client;
- added a local verification rate limit: 10 attempts / 10 minutes / client;
- rate-limit identities are HMAC-hashed; raw IP addresses are not stored;
- added a no-interaction honeypot field;
- retained atomic single-use replay protection;
- updated the compact floating verification UI for the new solver.

## License

MIT. See `LICENSE.TXT`.


## 1.1.1

- Fix upgrades from 1.0.x where the new rate-limit table was not created automatically.
- Add a lazy schema safeguard before rate-limit access.
- Log challenge-endpoint exceptions to the Concrete CMS log.
- Log non-sensitive verification diagnostics (expired/signature/solution flags) on failed submissions.


## 1.1.2

- replaced the package-owned PBKDF2 worker with ALTCHA Widget v3.2.1's official PBKDF2 worker, vendored locally under the MIT License;
- no browser asset or CAPTCHA request is loaded from ALTCHA, a CDN, or another external service at runtime;
- tuned invisible proof-of-work from cost 5000 / counter 5000–10000 to cost 1500 / counter 500–1200 to reduce form-submit latency, especially on mobile devices;
- start challenge solving on first form interaction so most proof-of-work is completed in the background before the visitor submits;
- retained the existing local challenge and verification rate limits, honeypot, key-signature verification and single-use replay protection;
- added the ALTCHA browser-worker MIT notice under `licenses/ALTCHA-MIT.txt`.
