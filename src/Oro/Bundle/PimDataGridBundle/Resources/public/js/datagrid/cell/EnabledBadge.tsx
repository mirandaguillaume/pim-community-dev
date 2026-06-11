import React from 'react';

type EnabledBadgeProps = {
  enabled: boolean;
  label: string;
};

/**
 * Presentational badge for the product "enabled" status column.
 * Reproduces the legacy template markup (templates/datagrid/cell/enabled-cell.html)
 * verbatim — the `AknBadge--{enabled,disabled}` and `status-{enabled,disabled}`
 * classes are part of the toggle-status Behat/CSS contract.
 */
const EnabledBadge = ({enabled, label}: EnabledBadgeProps) => {
  const status = enabled ? 'enabled' : 'disabled';

  return <div className={`AknBadge AknBadge--${status} status-${status}`}>{label}</div>;
};

export {EnabledBadge};
export type {EnabledBadgeProps};
