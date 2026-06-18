import React, {useCallback, useEffect, useState} from 'react';
import {SelectInput} from 'akeneo-design-system';
import ViewSelectorLine from './ViewSelectorLine';
import {ComboView, ensureDefaultView, idToValue, mergeViewPages, valueToId} from './viewComboboxHelpers';

type SearchResult = {
  views: ComboView[];
  more: boolean;
};

type Labels = {
  open: string;
  emptyResult: string;
  placeholder: string;
  publicLabel: string;
};

type Props = {
  /** The currently-selected view (controlled). Always rendered as an option so the DSM
   *  `currentValueElement` shows it even when it is not on the current search page. */
  currentView: ComboView | null;
  /** The synthetic "Default view" (id 0), with its label already translated by the host. */
  defaultView: ComboView;
  /** Whether to prepend the synthetic default view (legacy: no saved user default). */
  showDefaultView: boolean;
  /** Paged server search, injected by the host (wraps the datagrid-view fetcher). */
  searchViews: (term: string, page: number) => Promise<SearchResult>;
  /** Called when a view is picked; the host performs DatagridState.set + reloadPage + mediator. */
  onSelectView: (view: ComboView) => void;
  /** Whether the current view has unsaved filter/column changes (computed by the host). */
  dirty: boolean;
  labels: Labels;
};

/**
 * React combobox that replaces the legacy Select2 widget of the product-grid view-selector
 * (C1 Slice C). Built on DSM `SelectInput` in server-search mode (`disableInternalSearch`):
 * `onSearchChange` resets to page 1, `onNextPage` accumulates pages (cross-page de-duplicated),
 * and each option renders the already-React `ViewSelectorLine`. Selection is delegated to the host
 * via `onSelectView` (which owns DatagridState/reloadPage/mediator).
 *
 * Behat contract (Approach A — keep Select2 classnames):
 *   - Root: `div.select2-container` → GridCapableDecorator anchor `.grid-view-selector .select2-container`
 *   - Each option: wrapped in `div.select2-result-label` → Select2Decorator `getAvailableValues/setValue`
 */
const ViewSelectorCombobox = ({
  currentView,
  defaultView,
  showDefaultView,
  searchViews,
  onSelectView,
  dirty,
  labels,
}: Props) => {
  const [views, setViews] = useState<ComboView[]>([]);
  const [term, setTerm] = useState('');
  const [page, setPage] = useState(1);
  const [more, setMore] = useState(false);

  const runSearch = useCallback(
    async (nextTerm: string, nextPage: number) => {
      const result = await searchViews(nextTerm, nextPage);

      setMore(result.more);
      setViews(previous => {
        const base = 1 === nextPage ? [] : previous;
        const merged = mergeViewPages(base, result.views);

        return 1 === nextPage && '' === nextTerm ? ensureDefaultView(merged, defaultView, showDefaultView) : merged;
      });
    },
    [searchViews, defaultView, showDefaultView]
  );

  const handleSearchChange = useCallback(
    (nextTerm: string) => {
      setTerm(nextTerm);
      setPage(1);
      runSearch(nextTerm, 1);
    },
    [runSearch]
  );

  const handleNextPage = useCallback(() => {
    if (!more) {
      return;
    }

    const nextPage = page + 1;
    setPage(nextPage);
    runSearch(term, nextPage);
  }, [more, page, term, runSearch]);

  // Eager first-page load so opening the dropdown shows results immediately. Select2 fetched on
  // open; DSM SelectInput exposes no onOpen hook, so mount-load is the pragmatic equivalent.
  // `searchViews` must be stable (host useCallback) so this runs once per mount.
  useEffect(() => {
    runSearch('', 1);
  }, [runSearch]);

  const handleChange = (value: string) => {
    const id = valueToId(value);
    // Prefer canonical server-fetched objects and the synthetic defaultView over currentView,
    // which is mutated by the host's onGridStateChange to reflect the live filter/column state.
    // Selecting the same view should restore its original saved state, not re-apply local changes.
    const picked = [...views, defaultView, currentView].find(view => null !== view && view.id === id);

    if (picked) {
      onSelectView(picked);
    }
  };

  // The current view must always be an option, otherwise SelectInput.currentValueElement falls back
  // to the raw id string when the selected view is absent from the current search page.
  const options =
    null !== currentView && !views.some(view => view.id === currentView.id) ? [currentView, ...views] : views;

  return (
    <div className="select2-container">
      <SelectInput
        clearable={false}
        value={null !== currentView ? idToValue(currentView.id) : null}
        onChange={handleChange}
        disableInternalSearch={true}
        onSearchChange={handleSearchChange}
        onNextPage={handleNextPage}
        emptyResultLabel={labels.emptyResult}
        openLabel={labels.open}
        placeholder={labels.placeholder}
      >
        {options.map(view => {
          const isCurrent = null !== currentView && currentView.id === view.id;
          return (
            <SelectInput.Option key={view.id} value={idToValue(view.id)} title={view.text}>
              <div className="select2-result-label">
                <ViewSelectorLine
                  view={{id: view.id, text: view.text, type: view.type}}
                  isCurrent={isCurrent}
                  publicLabel={labels.publicLabel}
                  dirty={isCurrent && dirty}
                />
              </div>
            </SelectInput.Option>
          );
        })}
      </SelectInput>
    </div>
  );
};

export default ViewSelectorCombobox;
