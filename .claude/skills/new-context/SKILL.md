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
