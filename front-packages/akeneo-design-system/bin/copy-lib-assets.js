#!/usr/bin/env node
// Copies the non-TypeScript assets of src/ into both builds, at the same relative path.
//
// tsc compiles `import Picture from '../static/illustrations/Picture.svg'` into a require of that
// exact path but never copies the file. The path stays inside the package since the illustrations
// moved under src/, so shipping the file next to the compiled code is all a consumer needs.
const fs = require('fs');
const path = require('path');

const root = path.resolve(__dirname, '..');
const source = path.join(root, 'src', 'static');
const destinations = [path.join(root, 'lib', 'static'), path.join(root, 'lib', 'esm', 'static')];

let copied = 0;
for (const destination of destinations) {
  fs.rmSync(destination, {recursive: true, force: true});
  fs.cpSync(source, destination, {recursive: true});
  copied += fs.readdirSync(path.join(destination, 'illustrations')).length;
}

console.log(`Copied ${copied} assets into ${destinations.map(d => path.relative(root, d)).join(' and ')}`);
