import { spawnSync } from 'node:child_process';
import { resolve } from 'node:path';

const lighthousePath = resolve(process.argv[2] || 'lighthouse.json');
const failuresPath = resolve(process.argv[3] || 'contrast-failures.json');
const resultsPath = resolve(process.argv[4] || 'contrast-fix-results.json');

const extract = spawnSync('node', ['scripts/extract-contrast-failures.mjs', lighthousePath, failuresPath], {
    stdio: 'inherit',
});

if (extract.status !== 0) {
    process.exit(extract.status ?? 1);
}

const fix = spawnSync('node', ['scripts/fix-contrast-failures.mjs', failuresPath, resultsPath], {
    stdio: 'inherit',
});

process.exit(fix.status ?? 1);
