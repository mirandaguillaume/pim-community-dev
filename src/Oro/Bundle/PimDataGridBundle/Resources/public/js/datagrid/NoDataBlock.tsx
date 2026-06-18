import React from 'react';
import __ from 'oro/translator';

type Props = {
  hintKey: string;
  hintParams?: Record<string, string>;
  subHintKey: string;
  imageClass?: string;
};

/**
 * Empty-state block rendered inside the `.no-data` shell of the grid template
 * (C1 wave 3). Replaces the Underscore `pim/template/common/no-data` template.
 *
 * All text is produced by calling the translator directly — no HTML injection
 * needed. The legacy `\n` → `<br />` substitution was a no-op in practice
 * because the Underscore `<%-` tag HTML-escaped the output anyway.
 */
const NoDataBlock = ({hintKey, hintParams = {}, subHintKey, imageClass = ''}: Props) => (
  <div className="AknGridContainer-noData no-data-inner">
    <div className={`AknGridContainer-noDataImage ${imageClass}`.trimEnd()} />
    <div className="AknGridContainer-noDataTitle">{__(hintKey, hintParams)}</div>
    <div className="AknGridContainer-noDataSubtitle">{__(subHintKey)}</div>
  </div>
);

export {NoDataBlock};
