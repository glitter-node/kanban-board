import { readFileSync, writeFileSync } from 'node:fs';
import { resolve } from 'node:path';

function classify(selector, snippet) {
    const source = `${selector} ${snippet}`.toLowerCase();

    if (source.includes('<button') || source.includes('btn-') || source.includes('type="button"') || source.includes('type="submit"')) {
        return 'interactive';
    }

    if (source.includes('ui-kicker') || source.includes('<label') || source.includes('tracking-[') || source.includes('uppercase')) {
        return 'text-secondary';
    }

    if (source.includes('text-xs') || source.includes('text-sm') || source.includes('<small') || source.includes('text-muted') || source.includes('text-muted-foreground')) {
        return 'text-small';
    }

    return 'content';
}

const inputPath = resolve(process.argv[2] || 'lighthouse.json');
const outputPath = resolve(process.argv[3] || 'contrast-failures.json');
const report = JSON.parse(readFileSync(inputPath, 'utf8'));
const items = report.lighthouseResult?.audits?.['color-contrast']?.details?.items
    ?? report.audits?.['color-contrast']?.details?.items
    ?? [];

const selectors = items.map((item) => ({
    selector: item.node?.selector || '',
    snippet: item.node?.snippet || '',
    classification: classify(item.node?.selector || '', item.node?.snippet || ''),
    contrastRatio: item.contrastRatio || item.node?.explanation || null,
}));

writeFileSync(outputPath, `${JSON.stringify(selectors, null, 2)}\n`);
console.log(`Wrote ${selectors.length} contrast failures to ${outputPath}`);
