#!/usr/bin/env bash
# Hook: PostToolUse on Edit|Write
# DISABLED — Host PHP is 8.1 but project needs 8.2+.
# php -l on 8.1 would reject valid 8.2 syntax (readonly classes, DNF types, etc.).
# Use Docker for syntax checks: docker-compose run --rm php php -l <file>
exit 0
