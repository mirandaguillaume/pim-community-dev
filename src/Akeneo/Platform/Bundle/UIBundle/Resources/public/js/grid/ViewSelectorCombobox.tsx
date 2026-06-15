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
  labels: Labels;
};

/**
 * React combobox that replaces the legacy Select2 widget of the product-grid view-selector
 * (C1 Slice C). Built on DSM `SelectInput` in server-search mode (`disableInternalSearch`):
 * `onSearchChange` resets to page 1, `onNextPage` accumulates pages (cross-page de-duplicated),
 * and each option renders the already-React `ViewSelectorLine`. Selection is delegated to the host
 * via `onSelectView` (which owns DatagridState/reloadPage/mediator).
 *
 * NOT wired yet — this PR ships the component + its unit tests in isolation; the host wiring and the
 * Behat-decorator rewrite land in a follow-up. Deferred to that PR: the 400ms search debounce, the
 * stale-response guard, and the imperative `close-selector` (needs a DSM `SelectInput` enhancement).
 */
const ViewSelectorCombobox = ({
  currentView,
  defaultView,
  showDefaultView,
  searchViews,
  onSelectView,
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
    const picked = [currentView, defaultView, ...views].find(view => null !== view && view.id === id);

    if (picked) {
      onSelectView(picked);
    }
  };

  // The current view must always be an option, otherwise SelectInput.currentValueElement falls back
  // to the raw id string when the selected view is absent from the current search page.
  const options =
    null !== currentView && !views.some(view => view.id === currentView.id) ? [currentView, ...views] : views;

  return (
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
      {options.map(view => (
        <SelectInput.Option key={view.id} value={idToValue(view.id)} title={view.text}>
          <ViewSelectorLine
            view={{id: view.id, text: view.text, type: view.type}}
            isCurrent={null !== currentView && currentView.id === view.id}
            publicLabel={labels.publicLabel}
          />
        </SelectInput.Option>
      ))}
    </SelectInput>
  );
};

export default ViewSelectorCombobox;
