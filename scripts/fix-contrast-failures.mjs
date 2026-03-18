import { readFileSync, readdirSync, statSync, writeFileSync, existsSync } from 'node:fs';
import { resolve, extname } from 'node:path';

const inputPath = resolve(process.argv[2] || 'contrast-failures.json');
const outputPath = resolve(process.argv[3] || 'contrast-fix-results.json');

const fileExtensions = new Set(['.php', '.blade.php', '.html', '.js', '.vue']);
const roots = ['resources/views', 'resources/js', 'app'];

function collectFiles(dir, files = []) {
    if (!existsSync(dir)) {
        return files;
    }

    for (const entry of readdirSync(dir)) {
        const fullPath = resolve(dir, entry);
        const stat = statSync(fullPath);

        if (stat.isDirectory()) {
            collectFiles(fullPath, files);
            continue;
        }

        const extension = entry.endsWith('.blade.php') ? '.blade.php' : extname(entry);

        if (fileExtensions.has(extension)) {
            files.push(fullPath);
        }
    }

    return files;
}

function uniquePush(list, value) {
    if (!list.includes(value)) {
        list.push(value);
    }
}

function normalizeClasses(content, replacements) {
    let updated = content;

    const classReplacements = [
        { pattern: /\btext-muted-foreground\b/g, replacement: 'text-secondary' },
        { pattern: /\btext-muted\b/g, replacement: 'text-secondary' },
        { pattern: /\bui-muted\b/g, replacement: 'text-secondary' },
        { pattern: /\bui-subtle\b/g, replacement: 'text-secondary' },
        { pattern: /\btext-ui-text-muted\b/g, replacement: 'text-secondary' },
        { pattern: /\btext-ui-text-secondary\b/g, replacement: 'text-secondary' },
        { pattern: /\bborder-primary\/\d+\b/g, replacement: 'border-border' },
        { pattern: /\bborder-[a-z-]+\/\d+\b/g, replacement: 'border-border' },
        { pattern: /\bbg-primary\/\d+\b/g, replacement: 'bg-primary' },
        { pattern: /\bbg-surface\/\d+\b/g, replacement: 'bg-surface' },
        { pattern: /\bbg-elevated\/\d+\b/g, replacement: 'bg-surface' },
        { pattern: /\bbg-muted\/\d+\b/g, replacement: 'bg-surface' },
        { pattern: /\bbg-[a-z-]+\/\d+\b/g, replacement: (match) => match.replace(/\/\d+$/, '') },
        { pattern: /\btext-[a-z-]+\/\d+\b/g, replacement: 'text-secondary' },
    ];

    for (const rule of classReplacements) {
        const next = updated.replace(rule.pattern, rule.replacement);

        if (next !== updated) {
            uniquePush(replacements, `${rule.pattern} -> ${typeof rule.replacement === 'string' ? rule.replacement : 'normalized-solid-color'}`);
            updated = next;
        }
    }

    return updated;
}

function patchSnippetSpecifics(content, failure, replacements) {
    let updated = content;
    const snippet = failure.snippet || '';
    const selector = failure.selector || '';
    const classification = failure.classification || 'content';

    if (snippet.includes('ui-kicker')) {
        const next = updated.replace(/\bui-kicker\b/g, 'ui-kicker text-secondary');
        if (next !== updated) {
            uniquePush(replacements, 'ui-kicker -> ui-kicker text-secondary');
            updated = next;
        }
    }

    if (classification === 'text-small' || classification === 'text-secondary' || classification === 'content') {
        const next = updated
            .replace(/\btext-xs text-muted\b/g, 'text-xs text-secondary')
            .replace(/\btext-xs text-muted-foreground\b/g, 'text-xs text-secondary')
            .replace(/\btext-sm text-muted\b/g, 'text-sm text-secondary')
            .replace(/\btext-sm text-muted-foreground\b/g, 'text-sm text-secondary');

        if (next !== updated) {
            uniquePush(replacements, 'small/body muted text -> text-secondary');
            updated = next;
        }
    }

    if (snippet.includes('border-') || selector.includes('border')) {
        const next = updated.replace(/\bborder-border-strong\b/g, 'border-border');
        if (next !== updated) {
            uniquePush(replacements, 'border-border-strong -> border-border');
            updated = next;
        }
    }

    if (snippet.includes('style=') && /color\s*:\s*#[0-9a-f]{3,8}/i.test(snippet)) {
        const next = updated.replace(/style="([^"]*?)color\s*:\s*#[0-9a-f]{3,8};?([^"]*?)"/gi, (match, before, after) => {
            const remaining = `${before} ${after}`.replace(/\s+/g, ' ').trim().replace(/^;|;$/g, '').trim();

            if (remaining.length > 0) {
                return `style="${remaining}" class="text-secondary"`;
            }

            return 'class="text-secondary"';
        });

        if (next !== updated) {
            uniquePush(replacements, 'inline color style -> class text-secondary');
            updated = next;
        }
    }

    return updated;
}

const failures = JSON.parse(readFileSync(inputPath, 'utf8'));
const files = roots.flatMap((root) => collectFiles(resolve(root)));
const updatedFiles = [];
const appliedReplacements = [];
const unmatchedFailures = [];

for (const file of files) {
    const original = readFileSync(file, 'utf8');
    let updated = normalizeClasses(original, appliedReplacements);

    for (const failure of failures) {
        updated = patchSnippetSpecifics(updated, failure, appliedReplacements);
    }

    if (updated !== original) {
        writeFileSync(file, updated);
        updatedFiles.push(file);
    }
}

for (const failure of failures) {
    const source = `${failure.selector} ${failure.snippet}`;
    const matched = appliedReplacements.length > 0 && (
        /\btext-muted\b|\btext-muted-foreground\b|\bui-muted\b|\bui-subtle\b|\bborder-[a-z-]+\/\d+\b|\bbg-[a-z-]+\/\d+\b|ui-kicker|style=/.test(source)
    );

    if (!matched) {
        unmatchedFailures.push(failure);
    }
}

const result = {
    updatedFiles,
    appliedReplacements,
    remainingFailures: unmatchedFailures,
};

writeFileSync(outputPath, `${JSON.stringify(result, null, 2)}\n`);
console.log(JSON.stringify(result, null, 2));
