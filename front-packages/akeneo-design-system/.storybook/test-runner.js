// Storybook test-runner config.
//
// On top of the built-in render smoke (every story is rendered headlessly and
// must not throw), we run axe-core accessibility checks per story via
// axe-playwright, enforced as a BASELINE RATCHET:
//
//   - `color-contrast` is globally deferred — it flags the brand palette (98
//     stories, "serious") and is a separate design decision, tracked for a
//     future dedicated pass. It is NOT checked here.
//   - `a11y-baseline.json` freezes the real semantic violations that already
//     existed when a11y testing was introduced (button-name, label, aria-*).
//     Those stories stay green so the suite can be adopted without a big-bang
//     fix.
//   - Any violation NOT in the baseline (a new story, or a regression on an
//     existing one) FAILS the job. The debt can only shrink, never grow.
//
// To accept a genuinely-unavoidable new violation, add its rule id under the
// story key in a11y-baseline.json. To pay down debt, remove entries — the
// "stale baseline" warning below tells you which are now fixable.
//
// The addon-a11y automatic axe run is disabled via `parameters.a11y.test:'off'`
// in preview.tsx, so this hook is the single axe runner (avoids the
// "Axe is already running" race). See [[dsm-storybook-test-runner]].
const {injectAxe, getViolations} = require('axe-playwright');
const baseline = require('./a11y-baseline.json');

// Rules we do not enforce at all (separate design decision). Disabled in the
// per-run axe options (object form) — `runOnly` selects rule SETS, and
// per-rule {enabled:false} subtracts from that set. (Disabling via a separate
// `axe.configure([{id,enabled:false}])` does NOT reliably survive a `runOnly`
// run, which let color-contrast slip through.)
const DEFERRED_RULES = ['color-contrast'];

module.exports = {
  async preVisit(page) {
    await injectAxe(page);
  },

  async postVisit(page, context) {
    const violations = await getViolations(page, '#storybook-root', {
      runOnly: {type: 'tag', values: ['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa']},
      rules: Object.fromEntries(DEFERRED_RULES.map(id => [id, {enabled: false}])),
    });

    const storyKey = `${context.title} / ${context.name}`;
    const accepted = new Set(baseline[storyKey] ?? []);
    const current = new Set(violations.map(v => v.id));

    // Violations present now but not in the baseline → regression or new story.
    const introduced = violations.filter(v => !accepted.has(v.id));
    // Baseline entries no longer violated → debt that can be removed.
    const fixed = [...accepted].filter(id => !current.has(id));

    if (fixed.length > 0) {
      console.log(
        `[A11Y] stale baseline for "${storyKey}": ${fixed.join(', ')} no longer ` +
          `violated — remove from a11y-baseline.json.`
      );
    }

    if (introduced.length > 0) {
      const detail = introduced
        .map(v => {
          const nodes = v.nodes.map(n => n.target.join(' ')).join('\n        ');
          return `  • ${v.id} (${v.impact}) — ${v.help}\n        ${nodes}`;
        })
        .join('\n');
      throw new Error(
        `Accessibility regression in story "${storyKey}":\n${detail}\n\n` +
          `Fix the violation, or — if it is unavoidable — add the rule id under ` +
          `"${storyKey}" in front-packages/akeneo-design-system/.storybook/a11y-baseline.json.`
      );
    }
  },
};
