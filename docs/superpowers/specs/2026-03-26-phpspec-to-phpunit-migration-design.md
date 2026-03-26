# PHPSpec to PHPUnit Migration — Design Spec

## Problem

PHPSpec is the primary unit testing framework with 1,979 specs across the codebase. This creates several issues:

- **Mutation testing** (Infection) doesn't work with PHPSpec due to adapter bugs (AnonymousClassReflection crashes)
- **Coverage collection** requires a separate config + third-party extension
- **Tooling** (Codecov, IDE coverage, CI reporting) all work natively with PHPUnit but not PHPSpec
- **Developer experience** — maintaining two test frameworks doubles the cognitive load

PHPUnit already exists in the project (46 Test.php files, 6.3% adoption) and all infrastructure supports it natively.

## Goal

Migrate all 1,979 PHPSpec specs to PHPUnit tests, progressively, bounded context by bounded context, using an automated conversion script for the 78% of mechanically-convertible specs.

## Scope

| In scope | Out of scope |
|----------|-------------|
| All *Spec.php → *Test.php conversion | Behat tests |
| Automated conversion script | PHPUnit integration/E2E tests |
| phpspec.yml.dist cleanup per context | New test creation |
| Removal of phpspec dependency (final phase) | Framework architecture changes |

## Audit Summary

**Total: 1,979 PHPSpec specs**

| Context | Specs | Existing Tests | Complexity |
|---------|------:|---------------:|:----------:|
| Pim + tests/back/Pim | 1,058 | 1 | Medium-Complex |
| Tool | 229 | 6 | Medium-Complex |
| Connectivity | 211 | 0 | Medium-Complex |
| Platform | 141 | 29 | Medium |
| identifier-generator | 69 | 0 | Medium |
| Category | 54 | 9 | Medium |
| Acceptance (tests/back) | 25 | 0 | Simple |
| Channel | 12 | 1 | Simple-Medium |
| UserManagement | 1 | 0 | Simple |

### PHPSpec Feature Usage

| Feature | Occurrences | Automatable |
|---------|------------|:-----------:|
| Prophecy mocking (willReturn/shouldBeCalled) | 19,813 | Yes |
| `beConstructedWith` / `beConstructedThrough` | 1,550 specs (78%) | Yes |
| `shouldImplement` / `shouldHaveType` | 1,462 specs (74%) | Yes |
| `let()` setup methods | 346 specs (17%) | Yes |
| Custom matchers (`getMatchers()`) | 45 specs (2.3%) | No (manual) |

## Strategy: Automated Conversion Script

### Transformation Rules

```
PHPSpec                                    PHPUnit
──────────────────────────────────────────────────────────────────
class FooSpec extends ObjectBehavior    →  class FooTest extends TestCase
function let(Dep $dep)                  →  setUp(): void + createMock(Dep)
$this->beConstructedWith($a, $b)        →  $this->sut = new Foo($a, $b)
$dep->method()->willReturn('x')         →  $dep->method('method')->willReturn('x')
$dep->method()->shouldBeCalled()        →  $dep->expects($this->once())->method('method')
$dep->method()->shouldNotBeCalled()     →  $dep->expects($this->never())->method('method')
$dep->method()->shouldBeCalledTimes(N)  →  $dep->expects($this->exactly(N))->method('method')
$this->getValue()->shouldReturn('x')    →  $this->assertSame('x', $this->sut->getValue())
$this->getValue()->shouldBe('x')        →  $this->assertSame('x', $this->sut->getValue())
$this->getValue()->shouldBeNull()       →  $this->assertNull($this->sut->getValue())
$this->getValue()->shouldBeLike($x)     →  $this->assertEquals($x, $this->sut->getValue())
$this->getValue()->shouldBeAnInstanceOf →  $this->assertInstanceOf(X, $this->sut->getValue())
$this->shouldThrow(Ex)->during('m',[$a])→  $this->expectException(Ex); $this->sut->m($a)
$this->shouldImplement(Interface::class) → $this->assertInstanceOf(Interface, $this->sut)
$this->shouldHaveType(Class::class)     →  $this->assertInstanceOf(Class, $this->sut)
function it_does_something()            →  public function testItDoesSomething(): void
```

### Script Design

- **Language**: PHP (can leverage nikic/php-parser for AST analysis)
- **Input**: Path to a `*Spec.php` file or a directory
- **Output**: Corresponding `*Test.php` file in the same directory structure
- **Mode**: Dry-run by default (show diff), `--write` to create files
- **Fallback**: Specs that can't be fully converted get a `// TODO: manual conversion needed` comment at the problematic line
- **Report**: Summary of converted/skipped/manual specs

### What the script handles (78% of specs)

- Class declaration and namespace conversion
- `let()` → `setUp()` with mock creation
- `beConstructedWith()` → SUT instantiation
- All Prophecy mock patterns → PHPUnit mock API
- All `should*` assertion patterns → PHPUnit assertions
- Method naming (`it_does_x` → `testItDoesX`)
- Use statements (add TestCase, remove ObjectBehavior)

### What requires manual conversion (22%)

- Custom matchers (`getMatchers()`) — 45 specs
- Complex `Argument::that()` closures — ~100 specs
- `beConstructedThrough()` (named constructors) — needs per-case review
- Specs with side effects in constructors
- Any spec the script can't parse cleanly → marked with TODO

## Migration Phases

### Phase 0: Script + Pilot Channel (12 specs)

**Goal**: Build the conversion script, validate on Channel context.

1. Write the conversion script (`tools/convert-phpspec-to-phpunit.php`)
2. Run on `src/Akeneo/Channel/back/tests/Specification/` (12 specs)
3. Run converted tests, ensure CI green
4. Document edge cases and manual fixes needed
5. Delete Spec.php files in batch after CI validation
6. Remove Channel suites from `phpspec.yml.dist`

**Success criteria**: All 12 Channel specs converted and passing as PHPUnit tests.

### Phase 1: Isolated Components (~70 specs)

- identifier-generator (69 specs) — self-contained, hexagonal architecture
- UserManagement (1 spec) — trivial

### Phase 2: Medium Contexts (~80 specs)

- Category (54 specs) — already has 9 Test.php
- Platform (13 specs in src/ + 128 in tests/back)

### Phase 3: Heavy Contexts (~440 specs)

- Connectivity (211 specs)
- Tool (229 specs)

### Phase 4: The Big One (~1,058 specs)

- Pim + tests/back/Pim (846 + 212 specs)
- Final cleanup: remove `phpspec/phpspec` from composer.json
- Remove `phpspec.yml.dist` and `phpspec-coverage.yml`
- Remove `friends-of-phpspec/phpspec-code-coverage`
- Remove `infection/phpspec-adapter`

## Per-Phase Process

Each phase follows the same workflow:

```
1. Run script on context          → generates *Test.php files
2. Run PHPUnit on new tests       → fix any conversion errors
3. CI green (PHPUnit + PHPSpec)   → both suites pass in parallel
4. Delete *Spec.php files         → batch removal
5. Update phpspec.yml.dist        → remove migrated suites
6. CI green (PHPUnit only)        → validate no regressions
7. Commit + PR                    → one PR per context
```

## File Organization

Converted tests follow the existing PHPUnit convention:

```
# PHPSpec (before)
src/Akeneo/Channel/back/tests/Specification/API/Query/FooSpec.php

# PHPUnit (after)
src/Akeneo/Channel/back/tests/Unit/API/Query/FooTest.php
```

The `Specification/` directory is renamed to `Unit/` to match PHPUnit conventions. Test suite registration moves from `phpspec.yml.dist` to `phpunit.xml.dist`.

## Risks and Mitigations

| Risk | Mitigation |
|------|-----------|
| Script produces incorrect conversions | Dry-run mode + manual review of each batch |
| Prophecy → PHPUnit mock API edge cases | Fallback to TODO comments, manual fix |
| CI regression during parallel coexistence | Both suites run until context fully migrated |
| Custom matchers hard to convert | Extract into shared PHPUnit assertion traits |
| Large PRs hard to review | One PR per bounded context, squash merge |

## Success Metrics

- All 1,979 specs converted to PHPUnit tests
- `phpspec/phpspec` removed from composer.json
- Infection mutation testing covers all unit tests
- Zero test count regression (same or more assertions)

## Estimated Timeline

| Phase | Specs | Estimated Duration |
|-------|------:|:------------------:|
| 0 — Script + Channel pilot | 12 | 1-2 weeks |
| 1 — Isolated components | 70 | 1 week |
| 2 — Medium contexts | 80 | 2 weeks |
| 3 — Heavy contexts | 440 | 3-4 weeks |
| 4 — Pim (the big one) | 1,058 | 4-6 weeks |
| **Total** | **1,979** | **~12-16 weeks** |

With the automated script handling 78% of conversions, the actual developer time is ~150-200 hours spread across the phases (vs 500-600h fully manual).
