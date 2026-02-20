#!/usr/bin/env bash
# Hook: PreToolUse on Bash (git commit / git push)
# Scans composer.lock for packages requiring PHP > 8.2.

set -euo pipefail

INPUT=$(cat)
COMMAND=$(echo "$INPUT" | jq -r '.tool_input.command // empty' 2>/dev/null) || exit 0

[ -z "$COMMAND" ] && exit 0
echo "$COMMAND" | grep -qE 'git\s+(commit|push)' || exit 0

LOCK_FILE="composer.lock"
[ -f "$LOCK_FILE" ] || exit 0

INCOMPATIBLE=$(python3 -c "
import json
with open('$LOCK_FILE') as f:
    data = json.load(f)
for pkg in data.get('packages', []) + data.get('packages-dev', []):
    php_req = pkg.get('require', {}).get('php', '') or pkg.get('require', {}).get('php-64bit', '')
    if php_req and ('^8.3' in php_req or '^8.4' in php_req or '>=8.3' in php_req):
        print(f'  {pkg[\"name\"]} {pkg[\"version\"]} requires php {php_req}')
" 2>/dev/null || true)

if [ -n "$INCOMPATIBLE" ]; then
    echo "{\"hookSpecificOutput\":{\"hookEventName\":\"PreToolUse\",\"additionalContext\":\"composer.lock contains packages incompatible with PHP 8.2:\n${INCOMPATIBLE}\nPin to compatible versions before committing.\"}}"
fi

exit 0
