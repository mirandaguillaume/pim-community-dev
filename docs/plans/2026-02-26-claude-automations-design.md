# Claude Code Automations Design

**Date**: 2026-02-26
**Status**: Approved

## Context

Akeneo PIM Community Edition codebase with an existing Claude Code setup:
- 11 MCP servers, 10 active hooks, 4 Claude-only skills, 8 agents, 0 plugins
- 6210 PHP files + 2648 JS/TS files
- PHP 8.2 / Symfony / React 17, Docker mandatory
- 5 test frameworks: PHPSpec, PHPUnit, Behat, Jest, Playwright

## Goals

Fill the gaps in the current setup to make Claude Code more performant:
- Zero PostToolUse hooks active (all disabled due to Docker overhead)
- Zero user-invocable skills
- Zero plugins installed
- Agent overlap and missing specializations
- No protection against sensitive file edits

## Design

### 1. Hooks (5 new)

All hooks go in `.claude/hooks/` with config in `.claude/settings.json`.

#### 1.1 prettier-on-edit (PostToolUse)

- **Matcher**: `Edit|Write|mcp__serena__replace_symbol_body|mcp__serena__insert_after_symbol|mcp__serena__insert_before_symbol`
- **Behavior**: Run `npx prettier --write` on the modified file
- **Filter**: Only `.ts`, `.tsx`, `.js`, `.jsx` files
- **Key detail**: Extract `file_path` from Edit/Write or `relative_path` from Serena tools

#### 1.2 block-sensitive-files (PreToolUse)

- **Matcher**: `Edit|Write|mcp__serena__replace_symbol_body|mcp__serena__insert_after_symbol|mcp__serena__insert_before_symbol`
- **Behavior**: Block edits to `.env*`, `composer.lock`, `yarn.lock`
- **Decision**: `block` with explanation message

#### 1.3 no-debug-statements (PreToolUse)

- **Matcher**: `Bash` (on `git commit`)
- **Behavior**: Scan staged diff for `dump()`, `dd()`, `var_dump()`, `console.log()`, `debugger;`
- **Decision**: Warning via `hookSpecificOutput` (does not block)

#### 1.4 large-file-warning (PreToolUse)

- **Matcher**: `Write`
- **Behavior**: Warn if content > 50,000 characters
- **Decision**: Warning only, does not block

#### 1.5 notification-sound (Notification)

- **Matcher**: `idle_prompt`
- **Behavior**: `printf '\a'` (terminal bell) when Claude is waiting for input

### 2. Skills (5 new)

All skills go in `.claude/skills/<name>/SKILL.md`.

| Skill | Invocation | Model-invocable | Description |
|-------|------------|-----------------|-------------|
| `/gen-spec` | Both | Yes | Generate PHPSpec for a PHP class. References testing-conventions skill. |
| `/run-tests` | Both | Yes | Smart test runner: detects PHPSpec/PHPUnit/Behat/Jest/Playwright from file path. |
| `/fix-cs` | Both | Yes | Auto-fix code style: php-cs-fixer (Docker) for PHP, yarn lint-fix for front. |
| `/new-context` | User-only | No | Scaffold bounded context. Delegates to scaffold-context agent. |
| `/ci-status` | Both | Yes | Check GitHub Actions CI status, diagnose failures. Claude invokes after push/PR. |

### 3. MCP Servers (2 new)

Added to `.mcp.json`.

| Server | Command | Purpose |
|--------|---------|---------|
| Playwright | `npx -y @anthropic-ai/mcp-playwright` | Interactive E2E test debugging |
| Docker | `npx -y @anthropic-ai/mcp-docker` | Direct container management (logs, exec, restart) |

### 4. Agents (9 total - full rewrite)

Replace existing 8 agents with a cleaner set of 9. All in `.claude/agents/`.

| Agent | Model | Tools | Skills | Role |
|-------|-------|-------|--------|------|
| explore | inherit | Read, Grep, Glob, Bash | - | Semantic code exploration (grepai + call graph) |
| review-pr | inherit | Read, Glob, Grep, Bash | all 4 | Complete PR review |
| security-audit | sonnet | Read, Grep, Glob | security-checklist | Dedicated security audit (OWASP, JWT, auth, secrets). Read-only. |
| fix-ci | inherit | Read, Write, Edit, Bash, Glob, Grep | - | Debug CI/CD pipeline failures |
| scaffold-context | inherit | Read, Write, Bash, Glob | akeneo-architecture | Scaffold hexagonal bounded context |
| test-gap-finder | haiku | Read, Glob, Grep, Bash | testing-conventions | Find untested code per bounded context |
| upgrade-advisor | inherit | Read, Glob, Grep, Bash, WebSearch, WebFetch | code-quality-checklist | Combined PHP + Symfony + front deps upgrade analysis |
| refactor-advisor | sonnet | Read, Glob, Grep, Bash | akeneo-architecture, code-quality-checklist | Code quality + Doctrine anti-patterns |
| create-agent | inherit | Read, Write, Bash, Glob, Grep, AskUserQuestion | - | Meta-agent to create new agents |

**Agents removed** (absorbed):
- `php-upgrade` → `upgrade-advisor`
- `symfony-upgrade` → `upgrade-advisor`
- `refactor-analyzer` → `refactor-advisor`
- `deep-explore` → `explore`

### 5. Plugins (3 new)

| Plugin | Purpose |
|--------|---------|
| php-lsp | PHP Language Server for diagnostics and navigation |
| frontend-design | Production-grade React UI components |
| commit-commands | `/commit` and `/commit-push-pr` workflows |

### 6. Bonus: Permissions cleanup

Simplify `settings.local.json` by replacing accumulated one-off patterns with broader rules.

### 7. Bonus: Headless CI review

Add a GitHub Actions step for automated PR review using Claude headless mode.

## Files Modified

- `.claude/settings.json` - Add PostToolUse, PreToolUse (Edit/Write matcher), Notification hooks
- `.claude/hooks/` - 5 new scripts
- `.claude/skills/` - 5 new skill directories
- `.claude/agents/` - 9 agent files (replace 8 existing)
- `.mcp.json` - Add 2 MCP servers
- `settings.local.json` - Permissions cleanup
