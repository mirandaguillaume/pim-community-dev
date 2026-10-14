import SelectFilterReact from 'oro/datafilter/select-filter-react';

/**
 * React inner-render of the product completeness datagrid filter.
 *
 * The legacy `product_completeness-filter.js` is a bare `SelectFilter.extend({})` — it adds nothing
 * to the select filter, it only exists so grid metadata can name a distinct type. `select` itself
 * already renders through React (`select-filter-react`, Vague B), so this bridge is the same bare
 * extend against the React parent and inherits its entire render: the controlled DSM SelectInput,
 * the operator handling and the value shape.
 *
 * Added ALONGSIDE `product_completeness-filter.js`; only the `product_completeness` and
 * `product_and_product_model_completeness` FilterTypeRegistry entries are re-pointed, exactly as
 * `datetime-filter-react` was added beside `datetime-filter.js`.
 */
export default SelectFilterReact.extend({});
