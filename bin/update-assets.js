/*
 * Copies the browser assets we take from the "altcha" npm package.
 *
 * The copied files should never be edited by hand:
 *
 * - to restore them, run "npm ci" and then "npm run update-assets";
 * - to upgrade them, run "npm i -E altcha@latest" and then "npm run update-assets".
 */
'use strict';

const fs = require('fs');
const path = require('path');

const packageDirectory = path.dirname(__dirname);
const altchaDirectory = path.join(packageDirectory, 'node_modules', 'altcha');
if (!fs.existsSync(altchaDirectory)) {
    console.error('The "altcha" package is not installed: run "npm ci" first.');
    process.exit(1);
}
const altchaVersion = JSON.parse(fs.readFileSync(path.join(altchaDirectory, 'package.json'), 'utf8')).version;

const assets = [
    {
        source: 'dist/workers/pbkdf2.js',
        destination: 'js/vendor/altcha-pbkdf2-worker.js',
        header: [
            '/*',
            ` * ALTCHA PBKDF2 worker v${altchaVersion}`,
            ` * Source: https://github.com/altcha-org/altcha/blob/v${altchaVersion}/dist/workers/pbkdf2.js`,
            ' * License: MIT',
            ' *',
            ' * Automatically copied from the "altcha" npm package: do not edit it by hand,',
            ' * run "npm run update-assets" instead.',
            ' */',
        ].join('\n') + '\n',
    },
    {
        source: 'LICENSE.txt',
        destination: 'licenses/ALTCHA-MIT.txt',
    },
];

let updated = 0;
for (const asset of assets) {
    const sourceFile = path.join(altchaDirectory, asset.source);
    const destinationFile = path.join(packageDirectory, asset.destination);
    const contents = (asset.header || '') + fs.readFileSync(sourceFile, 'utf8').replace(/\r\n/g, '\n').replace(/\n*$/, '\n');
    const currentContents = fs.existsSync(destinationFile) ? fs.readFileSync(destinationFile, 'utf8') : null;
    if (contents === currentContents) {
        console.log(`${asset.destination}: already up-to-date`);
        continue;
    }
    fs.mkdirSync(path.dirname(destinationFile), {recursive: true});
    fs.writeFileSync(destinationFile, contents);
    console.log(`${asset.destination}: updated`);
    updated++;
}

console.log(updated === 0 ? 'No file has been updated.' : `${updated} file(s) updated from altcha v${altchaVersion}.`);
