import { spawn } from 'node:child_process';
import { existsSync, mkdirSync, readFileSync } from 'node:fs';
import { resolve } from 'node:path';
import { setTimeout as delay } from 'node:timers/promises';

const host = '127.0.0.1';
const port = 9898;
const externalUrl = process.env.A11Y_AUDIT_URL;
const baseUrl = externalUrl || `http://${host}:${port}`;
const outputDir = resolve('.tmp');
const outputPath = resolve(outputDir, 'lighthouse-a11y.json');

if (!existsSync(outputDir)) {
    mkdirSync(outputDir, { recursive: true });
}

const server = externalUrl
    ? null
    : spawn('php', ['artisan', 'serve', `--host=${host}`, `--port=${port}`], {
        stdio: 'ignore',
    });

const cleanup = () => {
    if (server && !server.killed) {
        server.kill('SIGTERM');
    }
};

process.on('exit', cleanup);
process.on('SIGINT', () => {
    cleanup();
    process.exit(130);
});
process.on('SIGTERM', () => {
    cleanup();
    process.exit(143);
});

async function waitForServer(retries = 30) {
    for (let attempt = 0; attempt < retries; attempt++) {
        if (server && server.exitCode !== null) {
            throw new Error(`Local audit server exited before becoming ready. Set A11Y_AUDIT_URL to audit an existing environment instead.`);
        }

        try {
            const response = await fetch(baseUrl, { redirect: 'manual' });

            if (response.status < 500) {
                return;
            }
        } catch {
            // Server not ready yet.
        }

        await delay(1000);
    }

    throw new Error(`Timed out waiting for ${baseUrl}. Set A11Y_AUDIT_URL to audit an existing environment if local binding is unavailable.`);
}

function runLighthouse() {
    return new Promise((resolvePromise, rejectPromise) => {
        const child = spawn(
            resolve('node_modules/.bin/lighthouse'),
            [
                baseUrl,
                '--only-categories=accessibility',
                '--output=json',
                `--output-path=${outputPath}`,
                '--chrome-flags=--headless=new --no-sandbox',
                '--quiet',
            ],
            { stdio: 'inherit' },
        );

        child.on('exit', (code) => {
            if (code === 0) {
                resolvePromise();
                return;
            }

            rejectPromise(new Error(`Lighthouse exited with status ${code}`));
        });
    });
}

function validateReport() {
    const report = JSON.parse(readFileSync(outputPath, 'utf8'));
    const accessibilityScore = Math.round((report.categories.accessibility.score ?? 0) * 100);
    const failedContrast = Object.values(report.audits).filter(
        (audit) => audit.id === 'color-contrast' && audit.score !== 1,
    );

    if (accessibilityScore < 95) {
        throw new Error(`Accessibility score ${accessibilityScore} is below 95.`);
    }

    if (failedContrast.length > 0) {
        throw new Error('Lighthouse reported color contrast failures.');
    }

    console.log(`Accessibility score: ${accessibilityScore}`);
    console.log('No color contrast failures detected.');
}

await waitForServer();
await runLighthouse();
validateReport();
cleanup();
