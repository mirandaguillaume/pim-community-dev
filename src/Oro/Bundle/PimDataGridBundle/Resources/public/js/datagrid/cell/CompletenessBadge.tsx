import React from 'react';

type CompletenessLevel = 'success' | 'warning' | 'important';

type CompletenessBadgeProps = {
  level: CompletenessLevel;
  label: string;
};

/**
 * Presentational completeness badge shared by the completeness and
 * complete-variant-product cells. Reproduces the legacy inline markup
 * (`<span class="AknBadge AknBadge--{level}">…</span>`) verbatim so the grid CSS
 * contract is preserved.
 */
const CompletenessBadge = ({level, label}: CompletenessBadgeProps) => (
  <span className={`AknBadge AknBadge--${level}`}>{label}</span>
);

export {CompletenessBadge};
export type {CompletenessBadgeProps, CompletenessLevel};
