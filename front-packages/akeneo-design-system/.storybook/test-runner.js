// Storybook test-runner config.
//
// In addition to the built-in render smoke (every story is rendered headlessly
// and must not throw), we run axe-core accessibility checks per story via
// axe-playwright.
//
// DISCOVERY MODE (current): the 247 pre-existing stories were never a11y-audited,
// so we do NOT fail the job yet. `getViolations` collects violations WITHOUT
// throwing (unlike `checkA11y`), and we log one parseable line per story so the
// real violation inventory can be read from the CI logs. Once we know the
// landscape, this flips to a gated enforcing mode (allowlist / rule subset /
// baseline of accepted violations) — see [[dsm-storybook-test-runner]].
const {injectAxe, configureAxe, getViolations} = require('axe-playwright');

module.exports = {
  async preVisit(page) {
    await injectAxe(page);
  },

  async postVisit(page, context) {
    // WCAG 2.0/2.1 A & AA — the conventional baseline ruleset.
    await configureAxe(page, {
      // axe runs against the story root that Storybook renders into.
      // No global rule disabling in discovery mode: we want the full picture.
    });

    const violations = await getViolations(page, '#storybook-root', {
      runOnly: {type: 'tag', values: ['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa']},
    });

    const label = `${context.title} / ${context.name}`;
    if (violations.length === 0) {
      console.log(`[A11Y] OK   :: ${label}`);
      return;
    }

    // One machine-readable line per violating story: total node count + rule ids
    // with per-rule node counts and impact, so the CI log can be aggregated.
    const total = violations.reduce((n, v) => n + v.nodes.length, 0);
    const rules = violations.map(v => `${v.id}(${v.impact}:${v.nodes.length})`).join(',');
    console.log(`[A11Y] FAIL :: ${label} :: nodes=${total} :: ${rules}`);
  },
};
