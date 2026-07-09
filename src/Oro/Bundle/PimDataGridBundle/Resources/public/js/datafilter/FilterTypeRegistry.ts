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
  metric: 'oro/datafilter/metric-filter',
  multiselect: 'oro/datafilter/multiselect-filter',
  none: 'oro/datafilter/none-filter',
  number: 'oro/datafilter/number-filter-react',
  parent: 'oro/datafilter/parent-filter-react',
  price: 'oro/datafilter/price-filter',
  product_and_product_model_completeness: 'oro/datafilter/product_completeness-filter',
  product_category: 'oro/datafilter/product_category-filter',
  product_completeness: 'oro/datafilter/product_completeness-filter',
  product_scope: 'oro/datafilter/product_scope-filter',
  search: 'oro/datafilter/search-filter',
  attribute_search: 'oro/datafilter/search-filter',
  select: 'oro/datafilter/select-filter',
  'select-row': 'oro/datafilter/select-row-filter',
  'select2-choice': 'oro/datafilter/select2-choice-filter',
  'select2-rest-choice': 'oro/datafilter/select2-rest-choice-filter',
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
  selectrow: 'select-row',
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
