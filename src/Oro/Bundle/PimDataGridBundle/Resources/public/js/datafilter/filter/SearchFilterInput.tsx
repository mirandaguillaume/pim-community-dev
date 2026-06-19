import React from 'react';

type Props = {
  /** Placeholder text — the translated "Search <grid label>" string. */
  label: string;
  /** Initial value (uncontrolled — see below). Empty on first render, as in the legacy template. */
  value?: string;
};

/**
 * Presentational search-filter input for the datagrid filter bar (C1 Wave 4, Slice A — the first
 * React filter exemplar).
 *
 * Reproduces, byte-for-byte, the markup of the underscore template
 * `pim/template/datagrid/filter/search-filter` (templates/filter/search-filter.html): a single
 * `input.AknFilterBox-search[name="value"]` — the Behat contract (SearchDecorator reads
 * `.search-filter input[name="value"]`).
 *
 * The input is **uncontrolled** (`defaultValue`, not `value`): the Backbone shell `search-filter.js`
 * keeps owning the value path (its jQuery `_getInputValue`/`_setInputValue`/`enableReadonly` +
 * keydown/focus delegation). A React-controlled input would fight the shell's `.val()` writes and
 * would not observe Behat's synthetic jQuery `.trigger('change')`. React only renders the structure.
 */
const SearchFilterInput = ({label, value = ''}: Props) => (
  <input
    className="AknFilterBox-search"
    maxLength={255}
    autoComplete="non"
    type="text"
    name="value"
    defaultValue={value}
    placeholder={label}
  />
);

export default SearchFilterInput;
