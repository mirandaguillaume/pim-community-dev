---
name: gen-spec
description: Generate a PHPSpec test for a given PHP class following Akeneo conventions. Use when creating specs for new or existing classes.
---

Generate a PHPSpec test for the class at `$ARGUMENTS`.

## Context

- Reference the testing-conventions skill for PHPSpec patterns
- Current branch: !`git branch --show-current`

## Process

1. Read the target class using Serena's `find_symbol` with `include_body=true`
2. Identify the constructor parameters and public methods
3. Determine the correct spec location:
   - Modern context: `src/Akeneo/{Domain}/{Context}/back/tests/Specification/` mirroring source path
   - Legacy: `tests/back/Pim/` or `src/Akeneo/*/Bundle/*/spec/` mirroring source path
   - Check existing specs nearby for the correct pattern
4. Generate the spec following Akeneo conventions:
   - `let()` method with `$this->beConstructedWith(...)` for constructor args
   - `it_is_initializable()` test
   - One test per public method using `shouldReturn()`, `shouldThrow()`, etc.
   - Mock interfaces with Prophecy (`$mock->beADoubleOf(InterfaceName::class)`)
   - Namespace must mirror source namespace prefixed with `spec\`
5. Write the spec file
6. Run: `docker-compose run --rm php php vendor/bin/phpspec run <spec-path>`
7. Fix any failures and re-run until green
