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
