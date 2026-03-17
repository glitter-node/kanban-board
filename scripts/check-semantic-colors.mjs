import { execFileSync } from 'node:child_process';

const checks = [
    {
        pattern: String.raw`text-ui-text-|text-ui-brand|bg-ui-|bg-(gray|slate|zinc|neutral|stone|red|orange|amber|yellow|lime|green|emerald|teal|cyan|sky|blue|indigo|violet|purple|fuchsia|pink|rose)-|text-(gray|slate|zinc|neutral|stone|red|orange|amber|yellow|lime|green|emerald|teal|cyan|sky|blue|indigo|violet|purple|fuchsia|pink|rose)-|border-(gray|slate|zinc|neutral|stone|red|orange|amber|yellow|lime|green|emerald|teal|cyan|sky|blue|indigo|violet|purple|fuchsia|pink|rose)-|/[1-4][0-9]\b|backdrop-blur|mix-blend-mode|bg-clip-text|text-transparent`,
        description: 'legacy color classes or prohibited opacity/effect usage',
        include: [
            'resources/views',
            'resources/js',
        ],
    },
    {
        pattern: String.raw`#[0-9A-Fa-f]{3,8}(?=["'\s,;)}])`,
        description: 'raw hex colors outside token definitions',
        include: [
            'resources/views',
            'resources/js',
            'resources/css/app.css',
            'tailwind.config.js',
        ],
    },
];

const exclude = [
    '--glob',
    '!resources/views/mail/**',
    '--glob',
    '!resources/views/vendor/**',
];

for (const check of checks) {
    try {
        const output = execFileSync('rg', [
            '-n',
            '--pcre2',
            check.pattern,
            ...check.include,
            ...exclude,
        ], {
            cwd: process.cwd(),
            encoding: 'utf8',
            stdio: ['ignore', 'pipe', 'pipe'],
        });

        if (output.trim() !== '') {
            console.error(`Found ${check.description}:`);
            console.error(output.trim());
            process.exit(1);
        }
    } catch (error) {
        if (error.status === 1) {
            continue;
        }

        throw error;
    }
}

console.log('Semantic color validation passed.');
