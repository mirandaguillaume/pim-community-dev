# Claude Code Automations Implementation Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Implement 24 Claude Code automations (hooks, skills, MCP servers, agents, plugins) + permissions cleanup.

**Architecture:** Configuration-only changes across `.claude/` directory, `.mcp.json`, and plugin installs. No application code changes. Agents are a full rewrite (9 replace 8). Hooks cover both native Edit/Write and Serena MCP tools.

**Tech Stack:** Bash scripts (hooks), Markdown/YAML (skills, agents), JSON (settings, MCP config)

---

## Phase 1: Hooks (5 scripts + settings.json update)

Tasks in this phase are independent and can be parallelized (except Task 1.6 which depends on all scripts existing).

### Task 1.1: Create prettier-on-edit hook

**Files:**
- Create: `.claude/hooks/prettier-on-edit.sh`

**Step 1: Write the hook script**

```bash
#!/usr/bin/env bash
# Hook: PostToolUse on Edit|Write|Serena edit tools
# Auto-formats JS/TS files with Prettier after edit.
set -euo pipefail

INPUT=$(cat)

# Extract file path: native tools use file_path, Serena uses relative_path
FILE=$(echo "$INPUT" | jq -r '.tool_input.file_path // .tool_input.filePath // empty' 2>/dev/null)
if [ -z "$FILE" ]; then
    REL=$(echo "$INPUT" | jq -r '.tool_input.relative_path // empty' 2>/dev/null)
    if [ -n "$REL" ]; then
        FILE="${CLAUDE_PROJECT_DIR}/${REL}"
    fi
fi

[ -z "$FILE" ] && exit 0
[ -f "$FILE" ] || exit 0

case "$FILE" in
    *.ts|*.tsx|*.js|*.jsx)
        npx prettier --config "${CLAUDE_PROJECT_DIR}/.prettierrc.json" --write "$FILE" 2>/dev/null || true
        ;;
esac
exit 0
```

**Step 2: Make executable**

Run: `chmod +x .claude/hooks/prettier-on-edit.sh`

**Step 3: Verify script syntax**

Run: `bash -n .claude/hooks/prettier-on-edit.sh`
Expected: No output (valid syntax)

---

### Task 1.2: Create block-sensitive-files hook

**Files:**
- Create: `.claude/hooks/block-sensitive-files.sh`

**Step 1: Write the hook script**

```bash
#!/usr/bin/env bash
# Hook: PreToolUse on Edit|Write|Serena edit tools
# Blocks edits to .env files and lock files.
set -euo pipefail

INPUT=$(cat)

FILE=$(echo "$INPUT" | jq -r '.tool_input.file_path // .tool_input.filePath // empty' 2>/dev/null)
if [ -z "$FILE" ]; then
    REL=$(echo "$INPUT" | jq -r '.tool_input.relative_path // empty' 2>/dev/null)
    if [ -n "$REL" ]; then
        FILE="${CLAUDE_PROJECT_DIR}/${REL}"
    fi
fi

[ -z "$FILE" ] && exit 0

BASENAME=$(basename "$FILE")
case "$BASENAME" in
    .env|.env.*)
        echo '{"decision":"block","reason":"Protected file: '"$BASENAME"'. Edit .env files manually."}'
        ;;
    composer.lock|yarn.lock)
        echo '{"decision":"block","reason":"Protected file: '"$BASENAME"'. Use composer/yarn to update lock files."}'
        ;;
esac
exit 0
```

**Step 2: Make executable**

Run: `chmod +x .claude/hooks/block-sensitive-files.sh`

**Step 3: Verify script syntax**

Run: `bash -n .claude/hooks/block-sensitive-files.sh`
Expected: No output (valid syntax)

---

### Task 1.3: Create no-debug-statements hook

**Files:**
- Create: `.claude/hooks/no-debug-statements.sh`

**Step 1: Write the hook script**

```bash
#!/usr/bin/env bash
# Hook: PreToolUse on Bash (git commit)
# Warns about debug statements in staged changes.
set -euo pipefail

INPUT=$(cat)
COMMAND=$(echo "$INPUT" | jq -r '.tool_input.command // empty' 2>/dev/null) || exit 0

[ -z "$COMMAND" ] && exit 0
echo "$COMMAND" | grep -qE 'git\s+commit' || exit 0

# Scan staged diff for debug statements
FOUND=$(git diff --cached -U0 2>/dev/null \
    | grep -E '^\+' \
    | grep -v '^\+\+\+' \
    | grep -E '(dump\(|dd\(|var_dump\(|console\.log\(|debugger;)' \
    || true)

if [ -n "$FOUND" ]; then
    COUNT=$(echo "$FOUND" | wc -l)
    echo "{\"hookSpecificOutput\":{\"hookEventName\":\"PreToolUse\",\"additionalContext\":\"DEBUG STATEMENTS: $COUNT occurrence(s) of dump()/dd()/var_dump()/console.log()/debugger in staged changes. Remove before committing.\"}}"
fi
exit 0
```

**Step 2: Make executable**

Run: `chmod +x .claude/hooks/no-debug-statements.sh`

**Step 3: Verify script syntax**

Run: `bash -n .claude/hooks/no-debug-statements.sh`
Expected: No output (valid syntax)

---

### Task 1.4: Create large-file-warning hook

**Files:**
- Create: `.claude/hooks/large-file-warning.sh`

**Step 1: Write the hook script**

```bash
#!/usr/bin/env bash
# Hook: PreToolUse on Write
# Warns when writing files larger than 50KB.
set -euo pipefail

INPUT=$(cat)
CONTENT=$(echo "$INPUT" | jq -r '.tool_input.content // empty' 2>/dev/null)
FILE=$(echo "$INPUT" | jq -r '.tool_input.file_path // .tool_input.filePath // empty' 2>/dev/null)

[ -z "$CONTENT" ] || [ -z "$FILE" ] && exit 0

SIZE=${#CONTENT}
if [ "$SIZE" -gt 50000 ]; then
    BASENAME=$(basename "$FILE")
    echo "{\"hookSpecificOutput\":{\"hookEventName\":\"PreToolUse\",\"additionalContext\":\"LARGE FILE WARNING: About to write ${SIZE} chars to $BASENAME. Verify this is intentional and not a generated/binary file.\"}}"
fi
exit 0
```

**Step 2: Make executable**

Run: `chmod +x .claude/hooks/large-file-warning.sh`

**Step 3: Verify script syntax**

Run: `bash -n .claude/hooks/large-file-warning.sh`
Expected: No output (valid syntax)

---

### Task 1.5: Notification sound hook

This hook is inline in settings.json (no script needed). Implemented in Task 1.6.

---

### Task 1.6: Update settings.json with all new hooks

**Files:**
- Modify: `.claude/settings.json`

**Step 1: Add new hook matchers to settings.json**

The file currently has only `PreToolUse` with matcher `Bash`. We need to add:
- A `PreToolUse` entry with matcher `Edit|Write|mcp__serena__replace_symbol_body|mcp__serena__insert_after_symbol|mcp__serena__insert_before_symbol` for block-sensitive-files
- A `PreToolUse` entry with matcher `Write` for large-file-warning
- A `PostToolUse` section with matcher for prettier-on-edit
- A `Notification` section for notification sound
- Add no-debug-statements to the existing Bash PreToolUse hooks

The new settings.json should be:

```json
{
  "hooks": {
    "PreToolUse": [
      {
        "matcher": "Bash",
        "hooks": [
          {
            "type": "command",
            "command": "$CLAUDE_PROJECT_DIR/.claude/hooks/enforce-docker-php.sh",
            "statusMessage": "Checking Docker usage..."
          },
          {
            "type": "command",
            "command": "$CLAUDE_PROJECT_DIR/.claude/hooks/warn-large-diff.sh",
            "statusMessage": "Checking commit size..."
          },
          {
            "type": "command",
            "command": "$CLAUDE_PROJECT_DIR/.claude/hooks/composer-platform-check.sh",
            "statusMessage": "Checking PHP platform compatibility..."
          },
          {
            "type": "command",
            "command": "$CLAUDE_PROJECT_DIR/.claude/hooks/cs-fixer-staged.sh",
            "statusMessage": "Checking code style on staged files...",
            "timeout": 120
          },
          {
            "type": "command",
            "command": "$CLAUDE_PROJECT_DIR/.claude/hooks/phpstan-staged.sh",
            "statusMessage": "Running PHPStan on staged files...",
            "timeout": 120
          },
          {
            "type": "command",
            "command": "$CLAUDE_PROJECT_DIR/.claude/hooks/phpspec-staged.sh",
            "statusMessage": "Running PHPSpec for staged files...",
            "timeout": 120
          },
          {
            "type": "command",
            "command": "$CLAUDE_PROJECT_DIR/.claude/hooks/eslint-staged.sh",
            "statusMessage": "Running ESLint on staged files...",
            "timeout": 60
          },
          {
            "type": "command",
            "command": "$CLAUDE_PROJECT_DIR/.claude/hooks/jest-staged.sh",
            "statusMessage": "Running Jest for staged files...",
            "timeout": 120
          },
          {
            "type": "command",
            "command": "$CLAUDE_PROJECT_DIR/.claude/hooks/no-debug-statements.sh",
            "statusMessage": "Checking for debug statements..."
          },
          {
            "type": "command",
            "command": "$CLAUDE_PROJECT_DIR/.claude/hooks/coupling-pre-push.sh",
            "statusMessage": "Checking architecture coupling...",
            "timeout": 180
          },
          {
            "type": "command",
            "command": "$CLAUDE_PROJECT_DIR/.claude/hooks/yarn-lint-pre-push.sh",
            "statusMessage": "Running ESLint on changed front files...",
            "timeout": 60
          }
        ]
      },
      {
        "matcher": "Edit|Write|mcp__serena__replace_symbol_body|mcp__serena__insert_after_symbol|mcp__serena__insert_before_symbol",
        "hooks": [
          {
            "type": "command",
            "command": "$CLAUDE_PROJECT_DIR/.claude/hooks/block-sensitive-files.sh",
            "statusMessage": "Checking for protected files..."
          }
        ]
      },
      {
        "matcher": "Write",
        "hooks": [
          {
            "type": "command",
            "command": "$CLAUDE_PROJECT_DIR/.claude/hooks/large-file-warning.sh",
            "statusMessage": "Checking file size..."
          }
        ]
      }
    ],
    "PostToolUse": [
      {
        "matcher": "Edit|Write|mcp__serena__replace_symbol_body|mcp__serena__insert_after_symbol|mcp__serena__insert_before_symbol",
        "hooks": [
          {
            "type": "command",
            "command": "$CLAUDE_PROJECT_DIR/.claude/hooks/prettier-on-edit.sh",
            "statusMessage": "Prettier auto-format..."
          }
        ]
      }
    ],
    "Notification": [
      {
        "matcher": "idle_prompt",
        "hooks": [
          {
            "type": "command",
            "command": "printf '\\a'"
          }
        ]
      }
    ]
  }
}
```

**Step 2: Verify JSON is valid**

Run: `python3 -c "import json; json.load(open('.claude/settings.json'))"`
Expected: No output (valid JSON)

**Step 3: Commit Phase 1**

```bash
git add .claude/hooks/prettier-on-edit.sh .claude/hooks/block-sensitive-files.sh .claude/hooks/no-debug-statements.sh .claude/hooks/large-file-warning.sh .claude/settings.json
git commit -m "feat(claude): add 5 new hooks (prettier, block-sensitive, no-debug, large-file, notification)"
```

---

## Phase 2: Skills (5 new SKILL.md files)

Tasks in this phase are independent and can be parallelized.

### Task 2.1: Create /gen-spec skill

**Files:**
- Create: `.claude/skills/gen-spec/SKILL.md`

**Step 1: Write the skill**

```markdown
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
```

**Step 2: Create skill directory and write file**

Run: `mkdir -p .claude/skills/gen-spec`

---

### Task 2.2: Create /run-tests skill

**Files:**
- Create: `.claude/skills/run-tests/SKILL.md`

**Step 1: Write the skill**

```markdown
---
name: run-tests
description: Smart test runner that detects the right test framework and runs tests. Use after writing code or when asked to run tests.
---

Run tests for `$ARGUMENTS` (file path, class name, or bounded context).

## Detection Rules

Detect the test type from the file path or name and run the appropriate command:

| Pattern | Framework | Command |
|---------|-----------|---------|
| `*Spec.php` | PHPSpec | `docker-compose run --rm php php vendor/bin/phpspec run <file>` |
| `*Integration.php` or `*Test.php` (in `tests/Integration/`) | PHPUnit Integration | `APP_ENV=test docker-compose run --rm php php vendor/bin/phpunit -c . --filter <class>` |
| `*Test.php` (in `tests/Acceptance/`) | PHPUnit Acceptance | `APP_ENV=test docker-compose run --rm php php vendor/bin/phpunit -c . --filter <class>` |
| `*.feature` | Behat | `make end-to-end-legacy O=<file>` |
| `*.test.ts` or `*.test.tsx` | Jest | `npx jest <file>` |
| `*.spec.ts` or `*.spec.tsx` | Jest | `npx jest <file>` |
| Tests in `tests/e2e/` | Playwright | `npx playwright test <file>` |

## If no file specified

1. Detect changed files: `git diff --name-only HEAD`
2. For each changed source file, find related test files
3. Run the detected tests grouped by framework

## If a source file (not a test) is specified

1. Find the matching test file(s):
   - PHP class → look for `*Spec.php` with matching name
   - TS/JS file → look for `*.test.ts(x)` with matching name
2. Run the found test(s)

## Bounded context shorthand

If argument is a context name (e.g., `Channel`, `Connectivity/Connection`):
- Run: `make {context-slug}-unit-back` for PHPSpec
- Run: `make {context-slug}-acceptance-back` for acceptance
```

---

### Task 2.3: Create /fix-cs skill

**Files:**
- Create: `.claude/skills/fix-cs/SKILL.md`

**Step 1: Write the skill**

```markdown
---
name: fix-cs
description: Auto-fix code style for PHP and JS/TS files. Use after editing code or when code style checks fail.
---

Fix code style for `$ARGUMENTS` (file path, directory, or "all").

## Process

### If PHP file(s) or directory

```bash
docker-compose run --rm php php tools/php-cs-fixer fix --config=.php-cs-fixer.dist.php <path>
```

If a specific file: pass `--path-mode=intersection <file>`.

### If JS/TS file(s)

```bash
npx prettier --config .prettierrc.json --write "<path>"
npx eslint --fix "<path>"
```

### If "all" or no argument

Run both:
1. `docker-compose run --rm php php tools/php-cs-fixer fix --config=.php-cs-fixer.dist.php`
2. `yarn lint-fix`

### After fixing

Report what was changed:
- Number of files fixed
- Run `git diff --stat` to show the changes
```

---

### Task 2.4: Create /new-context skill

**Files:**
- Create: `.claude/skills/new-context/SKILL.md`

**Step 1: Write the skill**

```markdown
---
name: new-context
description: Scaffold a new bounded context with hexagonal architecture. Delegates to scaffold-context agent.
disable-model-invocation: true
---

Scaffold a new bounded context: `$ARGUMENTS` (format: `Domain/ContextName`, e.g., `Pim/ProductExport`).

## Process

Delegate to the `scaffold-context` agent via Task tool with the following prompt:

"Scaffold a new bounded context at `src/Akeneo/{Domain}/{ContextName}/` following the hexagonal architecture pattern. Create the full directory structure, Bundle file, services.yml, coupling config, PHPStan config, and Make targets."

Pass the bounded context name from $ARGUMENTS.
```

---

### Task 2.5: Create /ci-status skill

**Files:**
- Create: `.claude/skills/ci-status/SKILL.md`

**Step 1: Write the skill**

```markdown
---
name: ci-status
description: Check GitHub Actions CI status for current branch and diagnose failures. Use after pushing code or creating a PR.
---

Check CI status for the current branch.

## Context

- Branch: !`git branch --show-current`
- Recent push: !`git log --oneline -1`

## Process

1. Get CI status:
   ```bash
   gh run list --branch $(git branch --show-current) --limit 5
   ```

2. If any run is in progress, report status and wait.

3. If any run failed:
   ```bash
   gh run view <run-id> --log-failed
   ```

4. For each failed job, identify:
   - Which CI job failed (lint-back, unit-back, behat-legacy, etc.)
   - The root cause from the logs
   - The local command to reproduce: see the job-to-command mapping below

5. Propose a fix or offer to delegate to the `fix-ci` agent.

## Job-to-Command Mapping

| CI Job | Local Command |
|--------|---------------|
| `back-static` / `lint-back` | `PIM_CONTEXT=test make lint-back` |
| `unit-back` / `phpspec` | `PIM_CONTEXT=test make unit-back` |
| `acceptance-back` | `PIM_CONTEXT=test make acceptance-back` |
| `coupling-back` / `code-style-back` | `PIM_CONTEXT=test make coupling-back` |
| `front-lint` | `yarn lint` |
| `front-unit` | `yarn unit` |
| `behat-legacy` | `make end-to-end-legacy O=<feature>` |
| `playwright` | `npx playwright test` |
```

**Step 2: Create all skill directories**

Run: `mkdir -p .claude/skills/gen-spec .claude/skills/run-tests .claude/skills/fix-cs .claude/skills/new-context .claude/skills/ci-status`

**Step 3: Commit Phase 2**

```bash
git add .claude/skills/gen-spec/ .claude/skills/run-tests/ .claude/skills/fix-cs/ .claude/skills/new-context/ .claude/skills/ci-status/
git commit -m "feat(claude): add 5 user-invocable skills (gen-spec, run-tests, fix-cs, new-context, ci-status)"
```

---

## Phase 3: MCP Servers (2 additions)

### Task 3.1: Add Playwright and Docker MCP servers to .mcp.json

**Files:**
- Modify: `.mcp.json`

**Step 1: Add the two new servers**

Add after the `time` entry in `.mcp.json`:

```json
"playwright": {"command": "npx", "args": ["-y", "@anthropic-ai/mcp-playwright"]},
"docker": {"command": "npx", "args": ["-y", "@anthropic-ai/mcp-docker"]}
```

**Step 2: Verify JSON is valid**

Run: `python3 -c "import json; json.load(open('.mcp.json'))"`
Expected: No output (valid JSON)

**Step 3: Commit Phase 3**

```bash
git add .mcp.json
git commit -m "feat(claude): add Playwright and Docker MCP servers"
```

---

## Phase 4: Agents (9 files - full rewrite)

Tasks in this phase are independent and can be parallelized. After writing all 9, delete the 4 old agent files that are being replaced.

### Task 4.1: Create explore agent

**Files:**
- Create: `.claude/agents/explore.md` (replaces `deep-explore.md`)

Content: Keep the same instructions from `deep-explore.md` but rename to `explore`. Same tools, same grepai workflow. Just a cleaner name.

---

### Task 4.2: Update review-pr agent

**Files:**
- Modify: `.claude/agents/review-pr.md`

No changes needed. The existing agent is already well-structured with all 4 skills loaded.

---

### Task 4.3: Create security-audit agent

**Files:**
- Create: `.claude/agents/security-audit.md`

```markdown
---
name: security-audit
description: Security audit agent for Akeneo PIM. Use for dedicated security review of code changes, PRs, or bounded contexts.
tools: Read, Grep, Glob
skills:
  - security-checklist
model: sonnet
---

## Instructions

Tu es un auditeur securite specialise pour Akeneo PIM. Tu effectues des audits dedies en utilisant le skill security-checklist.

### Perimetre d'audit

Analyse le code fourni pour les vulnerabilites suivantes :

#### SQL Injection
- Recherche : `createQueryBuilder`, `createNativeQuery`, `executeQuery`, DQL avec concatenation
- Pattern dangereux : `"SELECT ... WHERE " . $variable`
- Correction : Prepared statements, parameter binding

#### XSS (Cross-Site Scripting)
- Recherche : `|raw` dans Twig, `dangerouslySetInnerHTML` dans React
- Recherche : Outputs non-escapes dans les controllers
- Correction : Escaping automatique, sanitization

#### Authentication & Authorization Bypass
- Recherche : Controllers sans `#[IsGranted]` ou voter check
- Recherche : Routes publiques non intentionnelles
- Recherche : Token/session manipulation

#### JWT Misuse
- Recherche : Algorithm confusion (lcobucci/jwt)
- Recherche : Missing expiry validation
- Recherche : Hardcoded secrets

#### Secret Exposure
- Recherche : Hardcoded credentials, API keys dans le code source
- Recherche : Secrets dans les logs ou les reponses API
- Recherche : .env values in committed files

#### Insecure Deserialization
- Recherche : `unserialize()` avec input utilisateur
- Recherche : `json_decode` sans validation de schema

### Format du rapport

```markdown
## Security Audit Report

### Critical
| File:Line | Vulnerability | OWASP Category | Remediation |
|-----------|--------------|----------------|-------------|

### High
| File:Line | Vulnerability | OWASP Category | Remediation |
|-----------|--------------|----------------|-------------|

### Medium
| File:Line | Vulnerability | OWASP Category | Remediation |
|-----------|--------------|----------------|-------------|

### Low / Informational
| File:Line | Vulnerability | OWASP Category | Remediation |
|-----------|--------------|----------------|-------------|
```

### Processus

1. Identifier le perimetre (PR, fichiers, bounded context)
2. Scanner avec Grep pour les patterns dangereux
3. Lire le code source pour confirmer les faux positifs
4. Classer par severite (Critical > High > Medium > Low)
5. Proposer des remediations concretes avec du code

### Action

Analyse le perimetre fourni et genere un rapport de securite structure.
```

---

### Task 4.4: Update fix-ci agent

**Files:**
- Modify: `.claude/agents/fix-ci.md`

No changes needed. The existing agent is already well-structured.

---

### Task 4.5: Rename scaffold-bounded-context to scaffold-context

**Files:**
- Create: `.claude/agents/scaffold-context.md` (copy from `scaffold-bounded-context.md` with name change)
- Delete: `.claude/agents/scaffold-bounded-context.md` (after creating new file)

Change the frontmatter `name` to `scaffold-context` and `description` to match. Keep all instructions identical.

---

### Task 4.6: Create test-gap-finder agent

**Files:**
- Create: `.claude/agents/test-gap-finder.md`

```markdown
---
name: test-gap-finder
description: Analyze test coverage gaps in Akeneo PIM bounded contexts. Use to find untested code and prioritize test creation.
tools: Read, Glob, Grep, Bash
skills:
  - testing-conventions
model: haiku
---

## Instructions

Tu analyses la couverture de tests par bounded context dans Akeneo PIM.

### Processus

1. **Lister les bounded contexts** dans `src/Akeneo/`
2. **Pour chaque contexte**, compter :
   - Classes PHP source dans `Domain/`, `Application/`, `Infrastructure/`
   - Fichiers `*Spec.php` correspondants
   - Fichiers `*Integration.php` et `*Test.php`
3. **Calculer le ratio** specs/classes source
4. **Identifier** les classes publiques sans aucun test

### Commandes

```bash
# Compter les classes source d'un contexte
find src/Akeneo/{context}/back -name "*.php" \
    -not -name "*Spec.php" -not -name "*Test.php" \
    -not -name "*Integration.php" -not -path "*/tests/*" | wc -l

# Compter les specs
find src/Akeneo/{context} -name "*Spec.php" | wc -l

# Trouver les classes sans spec
for f in $(find src/Akeneo/{context}/back/Domain -name "*.php"); do
    CLASS=$(basename "$f" .php)
    SPEC=$(find . -name "${CLASS}Spec.php" 2>/dev/null)
    [ -z "$SPEC" ] && echo "UNTESTED: $f"
done
```

### Format du rapport

```markdown
## Test Coverage Report

### Summary
| Bounded Context | Source Classes | Specs | Integration | Coverage |
|-----------------|--------------|-------|-------------|----------|

### Untested Classes (Priority: Domain > Application > Infrastructure)
| Class | Layer | Bounded Context | Suggested Test Type |
|-------|-------|-----------------|---------------------|

### Recommendations
1. ...
```

### Priorite

- Domain classes > Application classes > Infrastructure classes
- Classes avec logique metier > classes de configuration
- Flag les contextes sous 60% de couverture spec
```

---

### Task 4.7: Create upgrade-advisor agent

**Files:**
- Create: `.claude/agents/upgrade-advisor.md` (merges `php-upgrade.md` + `symfony-upgrade.md`)

Combine the PHP version tables, Symfony version tables, Rector commands, deprecation patterns, and report formats from both existing agents into a single agent. Keep the `code-quality-checklist` and `akeneo-architecture` skills. Add front-end dependency checking (`yarn outdated`, `npm audit`).

The frontmatter:

```yaml
---
name: upgrade-advisor
description: Analyze PHP, Symfony, and front-end dependency compatibility. Use when planning upgrades, checking for deprecations, or auditing dependency health.
tools: Read, Glob, Grep, Bash, WebSearch, WebFetch
skills:
  - akeneo-architecture
  - code-quality-checklist
model: inherit
---
```

The body merges both existing agents plus adds a "Front-end Dependencies" section with `yarn outdated` and `npm audit` commands.

---

### Task 4.8: Create refactor-advisor agent

**Files:**
- Create: `.claude/agents/refactor-advisor.md` (merges `refactor-analyzer.md` + doctrine-analyzer)

Keep the existing refactor-analyzer instructions and add a "Doctrine Anti-Patterns" section covering:
- N+1 queries (foreach + find/getReference in loops)
- Raw SQL without parameter binding
- HYDRATE_OBJECT vs HYDRATE_ARRAY misuse
- Missing eager loading on frequently-accessed relations
- Missing indexes on queried columns

The frontmatter:

```yaml
---
name: refactor-advisor
description: Analyze code quality, suggest refactoring, and detect Doctrine ORM anti-patterns. Use for code quality reviews and performance analysis.
tools: Read, Glob, Grep, Bash
skills:
  - akeneo-architecture
  - code-quality-checklist
model: sonnet
---
```

---

### Task 4.9: Update create-agent agent

**Files:**
- Modify: `.claude/agents/create-agent.md`

No changes needed. The existing agent is already well-structured.

---

### Task 4.10: Remove old agent files

**Files:**
- Delete: `.claude/agents/deep-explore.md` (replaced by `explore.md`)
- Delete: `.claude/agents/php-upgrade.md` (replaced by `upgrade-advisor.md`)
- Delete: `.claude/agents/symfony-upgrade.md` (replaced by `upgrade-advisor.md`)
- Delete: `.claude/agents/refactor-analyzer.md` (replaced by `refactor-advisor.md`)
- Delete: `.claude/agents/scaffold-bounded-context.md` (replaced by `scaffold-context.md`)

**Step 1: Remove old files**

```bash
rm .claude/agents/deep-explore.md .claude/agents/php-upgrade.md .claude/agents/symfony-upgrade.md .claude/agents/refactor-analyzer.md .claude/agents/scaffold-bounded-context.md
```

**Step 2: Commit Phase 4**

```bash
git add .claude/agents/
git commit -m "feat(claude): rewrite agents (9 total, merging overlapping responsibilities)"
```

---

## Phase 5: Plugins (3 installs)

### Task 5.1: Install plugins

**Step 1: Install php-lsp**

Run: `claude plugins install php-lsp`

**Step 2: Install frontend-design**

Run: `claude plugins install frontend-design`

**Step 3: Install commit-commands**

Run: `claude plugins install commit-commands`

---

## Phase 6: Permissions cleanup

### Task 6.1: Clean up settings.local.json

**Files:**
- Modify: `.claude/settings.local.json`

**Step 1: Review current permissions**

The current file has ~120 one-off `Bash(for pr in ...)` patterns accumulated from previous sessions. Replace with broader patterns.

**Step 2: Simplify to broad patterns**

Keep the core patterns (docker, make, git, gh, etc.) and remove all the one-off `Bash(for ...)`, `Bash(do ...)`, `Bash(echo === ...)` entries. The goal is to go from ~120 Bash entries to ~30 clean ones.

**Step 3: Verify JSON validity**

Run: `python3 -c "import json; json.load(open('.claude/settings.local.json'))"`

Note: Do NOT commit settings.local.json (it's user-specific, not project-level).

---

## Execution Order

Phases 1-4 are independent and can be parallelized. Phase 5 (plugins) requires CLI interaction. Phase 6 is independent.

```
Phase 1 (Hooks) ──────┐
Phase 2 (Skills) ─────┤
Phase 3 (MCP) ────────┼── All parallel ──→ Phase 5 (Plugins) ──→ Phase 6 (Cleanup)
Phase 4 (Agents) ─────┘
```
