#!/usr/bin/env bash
# Hook: PostToolUse on Edit|Write
# When composer.json or composer.lock is modified, verify platform compatibility.
# Catches packages requiring PHP > 8.2 (like zipstream-php 3.2.1 requiring PHP 8.3).

set -euo pipefail

INPUT=$(cat)
FILE_PATH=$(echo "$INPUT" | jq -r '.tool_input.file_path // .tool_input.filePath // empty')

if [ -z "$FILE_PATH" ]; then
    exit 0
fi

# Only check composer files
BASENAME=$(basename "$FILE_PATH")
if [[ "$BASENAME" != "composer.json" && "$BASENAME" != "composer.lock" ]]; then
    exit 0
fi

# Quick check: scan composer.lock for PHP requirements > 8.2
if [[ "$BASENAME" == "composer.lock" ]] && [ -f "$FILE_PATH" ]; then
    # Extract packages requiring php >= 8.3 using python for reliable JSON parsing
    INCOMPATIBLE=$(python3 -c "
import json, re, sys
with open('$FILE_PATH') as f:
    data = json.load(f)
for pkg in data.get('packages', []) + data.get('packages-dev', []):
    php_req = pkg.get('require', {}).get('php', '') or pkg.get('require', {}).get('php-64bit', '')
    if php_req and ('^8.3' in php_req or '^8.4' in php_req or '>=8.3' in php_req):
        print(f\"  {pkg['name']} {pkg['version']} requires php {php_req}\")
" 2>/dev/null || true)

    if [ -n "$INCOMPATIBLE" ]; then
        echo "composer.lock contains packages incompatible with PHP 8.2:" >&2
        echo "$INCOMPATIBLE" >&2
        echo "Use composer config platform.php 8.2.30 or pin to compatible versions." >&2
        exit 2
    fi
fi

exit 0
