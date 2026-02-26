---
name: fix-cs
description: Auto-fix code style for PHP and JS/TS files. Use after editing code or when code style checks fail.
---

Fix code style for `$ARGUMENTS` (file path, directory, or "all").

## Process

### If PHP file(s) or directory

```bash
docker-compose run --rm php php vendor/bin/php-cs-fixer fix --config=.php-cs-fixer.dist.php <path>
```

If a specific file: pass `--path-mode=intersection <file>`.

### If JS/TS file(s)

```bash
npx prettier --config .prettierrc.json --write "<path>"
npx eslint --fix "<path>"
```

### If "all" or no argument

Run both:
1. `docker-compose run --rm php php vendor/bin/php-cs-fixer fix --config=.php-cs-fixer.dist.php`
2. `yarn lint-fix`

### After fixing

Report what was changed:
- Number of files fixed
- Run `git diff --stat` to show the changes
