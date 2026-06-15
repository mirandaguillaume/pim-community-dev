/**
 * Pure adapter helpers for the product-grid view-selector combobox (C1 Slice C).
 *
 * These reproduce the data semantics of the legacy Select2 `query()` in
 * `js/grid/view-selector.js` (paged server search, synthetic "Default view", `more` detection,
 * id stringification) as small, side-effect-free functions so they can be unit/mutation-tested in
 * isolation — independently of the DSM `SelectInput` rendering they will feed (wired in a later PR).
 */

export type ComboView = {
  id: number;
  text: string;
  type?: string;
  label?: string;
};

/** The number of views fetched per page (legacy `options.limit`). */
export const VIEW_PAGE_SIZE = 20;

/** The synthetic "Default view" id (legacy `ensureDefaultView` unshifts id 0). */
export const DEFAULT_VIEW_ID = 0;

/**
 * Legacy `toSelect2Format`: a raw view from the fetcher gets `text` (label fallback) and keeps its
 * numeric id/type. Defensive against a missing `text`.
 */
export const toComboView = (raw: {id: number; text?: string; label?: string; type?: string}): ComboView => ({
  id: raw.id,
  text: raw.text ?? raw.label ?? '',
  type: raw.type,
  label: raw.label,
});

/**
 * Legacy `more` detection: explicit `response.more` if present, else the "a full page came back"
 * heuristic (`results.length === pageSize`).
 */
export const hasMore = (response: {more?: boolean; results?: unknown[]}, pageSize: number = VIEW_PAGE_SIZE): boolean =>
  typeof response.more === 'undefined' ? (response.results?.length ?? 0) === pageSize : response.more;

/**
 * Append a freshly fetched page to the accumulated views, de-duplicating by id. DSM `SelectInput`
 * throws on duplicate `Option.value`, and the same view can reappear across pages, so cross-page
 * dedup is mandatory.
 */
export const mergeViewPages = (existing: ComboView[], incoming: ComboView[]): ComboView[] => {
  const seen = new Set(existing.map(view => view.id));

  return [...existing, ...incoming.filter(view => !seen.has(view.id))];
};

/**
 * Legacy `ensureDefaultView`: prepend the synthetic "Default view" — only on the first page of an
 * empty search, and only when the user has no saved default (the host decides `shouldShow`).
 */
export const ensureDefaultView = (views: ComboView[], defaultView: ComboView, shouldShow: boolean): ComboView[] =>
  shouldShow ? [defaultView, ...views] : views;

/** DSM `Option.value` is a string; view ids are numbers (id 0 is the synthetic default). */
export const idToValue = (id: number): string => String(id);

/** Parse a DSM `Option.value` back to a numeric view id. */
export const valueToId = (value: string): number => parseInt(value, 10);
