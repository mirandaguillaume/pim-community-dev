#!/usr/bin/env bash
# Hook: PostToolUse on Edit|Write
# DISABLED — Running PHPSpec via Docker on every edit is too slow (~10-30s per invocation).
# Use manually: docker-compose run --rm php php vendor/bin/phpspec run <spec-file>
exit 0
