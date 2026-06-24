import React, {useEffect, useRef, useState} from 'react';
import {createPortal} from 'react-dom';
import {GridFilter} from './filtersColumnHelpers';

type Props = {
  filtersLabel: string;
  doneLabel: string;
  loading: boolean;
  groupedFilters: {[group: string]: GridFilter[]};
  ignoredFilters: string[];
  onSearch: (value: string) => void;
  onToggleFilter: (name: string, checked: boolean) => void;
  onScrollBottom: () => void;
};

const ESCAPE = 27;
const ENTER = 13;
const SEARCH_DEBOUNCE_MS = 200;
const EXPAND_DELAY_MS = 100;

/**
 * The "Manage filters" panel of the product datagrid (C1 slice D/E, D1b) — the React render of the
 * legacy `filters-column.ts`.
 *
 * Reproduces, byte-for-byte, `templates/filter/filter-column.html` (the open button + the panel) and
 * `templates/filter/filter-group.html` (the grouped checkbox list), so the Behat "Manage filters"
 * decorator keeps resolving every selector: `.AknFilterBox-addFilterButton`, `.filter-list.select-filter-widget`,
 * `input[type="search"]`, `.filters-column`, `ul.ui-multiselect-checkboxes`, the per-filter
 * `input[id=name][value=name]` + `.close`.
 *
 * The panel is **portaled to `document.body`** — the legacy moved `.filter-list` there with `appendTo`,
 * and Behat resolves it from the page root, so `createPortal` is the faithful (and required) equivalent.
 *
 * Checkboxes and the search are ordinary CONTROLLED React: the Behat decorator toggles a filter with a
 * real Selenium `input->click()` and `setValue` on the search (not a jQuery `.trigger()`), so `onChange`
 * fires. The Backbone shell (`filters-column.ts`) keeps owning the `$.get` fetch, the filter list state
 * and the mediator contract; this component owns only the panel UI (open/close, search box, scroll).
 */
const FilterColumnPanel = ({
  filtersLabel,
  doneLabel,
  loading,
  groupedFilters,
  ignoredFilters,
  onSearch,
  onToggleFilter,
  onScrollBottom,
}: Props) => {
  const [opened, setOpened] = useState(false);
  const [expanded, setExpanded] = useState(false);
  const [search, setSearch] = useState('');
  const searchRef = useRef<HTMLInputElement>(null);
  const debounceRef = useRef<ReturnType<typeof setTimeout> | null>(null);

  // Open/close animation, mirroring the legacy togglePanel timing (expand + focus after a tick).
  useEffect(() => {
    if (!opened) {
      setExpanded(false);
      return;
    }

    const timer = setTimeout(() => {
      setExpanded(true);
      searchRef.current?.focus();
    }, EXPAND_DELAY_MS);

    return () => clearTimeout(timer);
  }, [opened]);

  const close = () => setOpened(false);

  const changeSearch = (value: string): void => {
    setSearch(value);

    if (null !== debounceRef.current) {
      clearTimeout(debounceRef.current);
    }
    debounceRef.current = setTimeout(() => onSearch(value), SEARCH_DEBOUNCE_MS);
  };

  const handleSearchKeyDown = (event: React.KeyboardEvent): void => {
    if (ESCAPE === event.keyCode) {
      setSearch('');
      onSearch('');
      close();
    } else if (ENTER === event.keyCode) {
      if (null !== debounceRef.current) {
        clearTimeout(debounceRef.current);
      }
      onSearch(search);
    }
  };

  const handleScroll = (event: React.UIEvent<HTMLDivElement>): void => {
    const list = event.currentTarget;
    if (list.scrollHeight - list.offsetHeight === Math.max(0, list.scrollTop)) {
      onScrollBottom();
    }
  };

  return (
    <>
      <button
        type="button"
        className="AknFilterBox-addFilterButton AknFilterBox-addFilterButton--asPanel"
        aria-haspopup="true"
        style={{width: '280px'}}
        data-toggle
        onClick={() => setOpened(isOpen => !isOpen)}
      >
        <div>{filtersLabel}</div>
      </button>
      {createPortal(
        <div
          className={`ui-multiselect-menu ui-widget ui-widget-content ui-corner-all AknFilterBox-addFilterButton AknFilterBox-column filter-list select-filter-widget pimmultiselect${
            expanded ? ' AknFilterBox-column--expanded' : ''
          }`}
          style={{display: opened ? 'block' : 'none'}}
          onScroll={handleScroll}
        >
          <div className="ui-multiselect-filter">
            <input
              ref={searchRef}
              placeholder=""
              type="search"
              value={search}
              onChange={event => changeSearch(event.target.value)}
              onKeyDown={handleSearchKeyDown}
            />
          </div>
          <div
            className="AknLoadingMask loading-mask filter-loading"
            style={{top: '50px', display: loading ? 'block' : 'none'}}
          />
          <div className="filters-column">
            {Object.entries(groupedFilters).map(([groupName, filters]) => (
              <ul key={groupName} className="ui-multiselect-checkboxes ui-helper-reset full">
                <li className="ui-multiselect-optgroup-label">
                  <a title={groupName}>{groupName}</a>
                </li>
                {filters.map(filter => (
                  <li key={filter.name}>
                    <label htmlFor={filter.name} title={filter.label} className="ui-corner-all ui-state-hover">
                      <input
                        id={filter.name}
                        name="multiselect_add-filter-select"
                        type="checkbox"
                        value={filter.name}
                        title={filter.label}
                        checked={filter.enabled}
                        disabled={ignoredFilters.includes(filter.name)}
                        aria-selected="true"
                        onChange={event => onToggleFilter(filter.name, event.target.checked)}
                      />
                      <span>{filter.label}</span>
                    </label>
                  </li>
                ))}
              </ul>
            ))}
          </div>
          <div className="AknColumn-bottomButtonContainer AknColumn-bottomButtonContainer--sticky">
            <div className="AknButton AknButton--apply close" onClick={close}>
              {doneLabel}
            </div>
          </div>
        </div>,
        document.body
      )}
    </>
  );
};

export default FilterColumnPanel;
