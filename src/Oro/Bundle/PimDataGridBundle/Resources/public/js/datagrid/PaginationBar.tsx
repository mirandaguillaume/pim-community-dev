import React from 'react';

type Handle = {
  label: string | number;
  title?: string;
  className?: string;
  wrapClass?: string;
};

type Props = {
  handles: Handle[];
  disabled: boolean;
};

/**
 * Presentational pagination bar for the legacy datagrid.
 *
 * Reproduces, byte-for-byte, the markup of the underscore template
 * `pim/template/datagrid/pagination` (templates/datagrid/pagination.html) so the
 * `AknActionButton`/`active`/`AknActionButton--disabled|highlight|unclickable` classes,
 * the `title="No. N"` attribute and the label text remain a stable Behat selector contract
 * across every grid that uses `oro/datagrid/pagination-input` (~20 grids).
 *
 * Purely presentational: the Backbone host (`pagination-input.js`) owns the page handles,
 * navigation and click delegation. Clicks bubble to the host's jQuery `events:{'click a'}`.
 */
const PaginationBar = ({handles, disabled}: Props) => (
  <>
    {handles.map((handle, index) => {
      const classes = ['AknActionButton', 'AknGridToolbar-actionButton'];
      if (handle.className) classes.push(handle.className);
      if (disabled) classes.push('disabled');

      return (
        <a key={index} className={classes.join(' ')} href="#" title={handle.title || undefined}>
          <span className={handle.wrapClass || undefined}>{handle.label}</span>
        </a>
      );
    })}
  </>
);

export default PaginationBar;
