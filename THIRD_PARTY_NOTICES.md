# Third-party notices

## ALTCHA PHP library

The PHP dependency `altcha-org/altcha` is installed with Composer and is distributed by the ALTCHA project under the MIT License.

## ALTCHA browser PBKDF2 worker

`js/vendor/altcha-pbkdf2-worker.js` is vendored from the ALTCHA Widget, in the version listed in the `package.json` file:

- Project: https://github.com/altcha-org/altcha
- Source file: `dist/workers/pbkdf2.js`
- Copyright (c) 2023-2026 Daniel Regeci, BAU Software s.r.o.
- License: MIT

A copy of the applicable MIT license is included at `licenses/ALTCHA-MIT.txt`.

The surrounding Concrete CMS form integration, UI, same-origin challenge loading, rate limiting, honeypot and replay protection are package-owned code. No runtime request is made to ALTCHA or another third-party service.
