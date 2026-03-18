#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"

PATTERN='bg-zinc-|bg-gray-|bg-white|bg-black|text-white|text-gray-|text-black|border-zinc-|border-gray-|dark:|style='

if rg -n "$PATTERN" \
    resources/views/auth \
    resources/views/boards \
    resources/views/components \
    resources/views/layouts \
    resources/views/livewire \
    resources/views/profile \
    -g '*.blade.php'; then
    echo
    echo "UI consistency check failed: forbidden UI patterns found."
    exit 1
fi

echo "UI consistency check passed."
