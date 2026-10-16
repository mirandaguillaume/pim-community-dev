/**
 * Explicit filter-type registry (Wave 4 prerequisite).
 *
 * Replaces the `{{type}}-filter` string template in `datafilter-builder.js`
 * and the `requireContext(\`oro/datafilter/${type}-filter\`)` interpolation in
 * `filters-selector.ts`. Both callers previously assembled AMD module IDs at
 * runtime from unverifiable string concatenation; this registry makes every
 * known filter type a compile-time constant.
 *
 * Wave 4 will extend this registry with React component co-registrations
 * alongside the Backbone AMD paths.
 */

/**
 * AMD module ID for each canonical filter type.
 * Keys are the *resolved* type names (after alias expansion, see FILTER_TYPE_ALIASES).
 */
export const FILTER_MODULE_IDS = {
  // NOTE: `product_category` is deliberately absent. It is not resolved through this registry: no
  // datagrid config declares that metadata type, and its two consumers --
  // `UIBundle/.../common/item-picker.js` and `UIBundle/.../product/grid/category-tree.js` -- import
  // `oro/datafilter/product_category-filter` directly, with a non-standard
  // `(urlParams, gridName, categoryBaseRoute, container, updateCallback)` signature. The requirejs
  // alias therefore stays; only this entry was dead weight. Re-adding it would make the registry
  // claim a resolution path that nothing uses.
  'ajax-choice': 'oro/datafilter/ajax-choice-filter',
  // C1 Wave 4 Slice C3: `choice` filters (reached by the `string` metadata type) render via React
  // (choice-filter-react extends the legacy choice-filter — the operator AknDropdown + Select2 value
  // field). The legacy `oro/datafilter/choice-filter` module stays for number/identifier/parent/uuid.
  choice: 'oro/datafilter/choice-filter-react',
  // C1 Wave 5: `date` filters render via React (date-filter-react extends the legacy date-filter — the
  // operator AknDropdown + the two jQuery Datepicker widgets shielded inside a memoized subtree).
  date: 'oro/datafilter/date-filter-react',
  // C1 Wave 5: `datetime` reuses date-filter-react (same DateFilterCriteria markup) with the time picker
  // options (pickTime) — datetime-filter-react extends date-filter-react exactly as the legacy did.
  datetime: 'oro/datafilter/datetime-filter-react',
  'grouped-variant': 'oro/datafilter/grouped-variant-filter',
  identifier: 'oro/datafilter/identifier-filter-react',
  label_or_identifier: 'oro/datafilter/label_or_identifier-filter',
  // C1 Wave 5: `metric` renders via React (sibling of `price`: number + a unit AknDropdown with i18n
  // labels, units fetched async; shared NumberUnitFilterCriteria).
  metric: 'oro/datafilter/metric-filter-react',
  multiselect: 'oro/datafilter/multiselect-filter-react',
  none: 'oro/datafilter/none-filter',
  number: 'oro/datafilter/number-filter-react',
  parent: 'oro/datafilter/parent-filter-react',
  // C1 Wave 5: `price` renders via React (number + a currency AknDropdown; shared NumberUnitFilterCriteria).
  price: 'oro/datafilter/price-filter-react',
  // `product_completeness` is a bare extend of `select`, which already renders via React, so both
  // completeness entries point at the equally bare `product_completeness-filter-react` bridge.
  product_and_product_model_completeness: 'oro/datafilter/product_completeness-filter-react',
  product_completeness: 'oro/datafilter/product_completeness-filter-react',
  // C1: `product_scope` renders via React (product_scope-filter-react extends select-filter-react but
  // keeps its own bespoke AknDropdown markup through ScopeFilterCriteria — the scope switcher lives in
  // the grid toolbar, it is not a criteria select). The legacy `oro/datafilter/product_scope-filter`
  // module stays on disk.
  product_scope: 'oro/datafilter/product_scope-filter-react',
  search: 'oro/datafilter/search-filter',
  attribute_search: 'oro/datafilter/search-filter',
  // Vague B: `select` and `multiselect` render via React (select-filter-react / multiselect-filter-react
  // extend the legacy filters — the jquery.multiselect widget replaced by the controlled DSM
  // SelectInput/MultiSelectInput). The legacy `oro/datafilter/select-filter` module stays because
  // `select-filter-react` itself extends it, as does the legacy `product_scope-filter` left on disk.
  select: 'oro/datafilter/select-filter-react',
  // C1 Wave 5: `select2-choice` renders via React (select2-choice-filter-react extends the legacy
  // select2-choice-filter — the operator AknDropdown + the multi-select ajax Select2 value field
  // shielded inside ChoiceFilterCriteria's memoized subtree).
  'select2-choice': 'oro/datafilter/select2-choice-filter-react',
  // C1 Wave 5: `select2-rest-choice` renders via React (sibling of select2-choice — same Select2 shape,
  // REST ajax value layer; select2-rest-choice-filter-react reuses ChoiceFilterCriteria, same as select2-choice).
  'select2-rest-choice': 'oro/datafilter/select2-rest-choice-filter-react',
  // C1 Wave 4 Slice C1: `text` filters render via React (text-filter-react extends the legacy
  // text-filter). The legacy `oro/datafilter/text-filter` module stays for the select2/ajax-choice
  // filters that `TextFilter.extend` it.
  text: 'oro/datafilter/text-filter-react',
  uuid: 'oro/datafilter/uuid-filter-react',
} as const;

export type CanonicalFilterType = keyof typeof FILTER_MODULE_IDS;
export type FilterModuleId = (typeof FILTER_MODULE_IDS)[CanonicalFilterType];

/**
 * Maps metadata type names (from Symfony YAML config) to canonical filter types.
 * When a metadata type is absent from this map, it IS the canonical type.
 *
 * Previously duplicated between `datafilter-builder.js` (complete) and
 * `filters-selector.ts` (missing `identifier`). Single source of truth here.
 */
export const FILTER_TYPE_ALIASES: Partial<Record<string, CanonicalFilterType>> = {
  identifier: 'identifier',
  string: 'choice',
  choice: 'select',
  multichoice: 'multiselect',
  boolean: 'select',
};

/**
 * Resolve a metadata filter type (from Symfony grid config) to its AMD module ID.
 *
 * @throws {Error} if the resolved type has no registered module — catches
 *   mis-configured YAML at boot time rather than silently producing a 404.
 */
export function resolveFilterModuleId(metadataType: string): FilterModuleId {
  const canonicalType = (FILTER_TYPE_ALIASES[metadataType] ?? metadataType) as CanonicalFilterType;
  const moduleId = FILTER_MODULE_IDS[canonicalType];

  if (!moduleId) {
    throw new Error(
      `FilterTypeRegistry: no module registered for filter type "${metadataType}"` +
        (metadataType !== canonicalType ? ` (resolved alias: "${canonicalType}")` : '')
    );
  }

  return moduleId;
}
