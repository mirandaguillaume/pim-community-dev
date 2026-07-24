import _ from 'underscore';
import SelectFilterReact from 'oro/datafilter/select-filter-react';

/**
 * React inner-render of the `multiselect` datagrid filter (Vague B, wave 2). Extends the PR1
 * `select-filter-react` bridge — inheriting the React mount/unmount, the `this._selectedValues`
 * (string[]) source of truth, and the whole value plumbing — and adds only the two multiselect
 * specifics:
 *  1. `widgetOptions.multiple = true`, so the inherited `_renderReact` renders the DSM
 *     `MultiSelectInput` (via `SelectFilterCriteria`'s `multiple` prop) instead of `SelectInput`.
 *  2. the "All" (`''`) mutual-exclusion previously in legacy `multiselect-filter._onSelectChange`.
 *
 * Added ALONGSIDE `multiselect-filter.js`; only the `multiselect` FilterTypeRegistry alias is
 * re-pointed. The legacy `classes` string drove the (now removed) jQuery widget and is intentionally
 * dropped — the inherited `render()` never calls `_initializeSelectWidget`, so only `multiple` is read.
 */
export default SelectFilterReact.extend({
  /**
   * Multi mode: the inherited `_renderReact` reads `this.widgetOptions.multiple` to pick
   * `MultiSelectInput` over `SelectInput`.
   *
   * @property
   */
  widgetOptions: {
    multiple: true,
  },

  /**
   * {@inheritdoc}
   *
   * The multi value is the WHOLE selection array (the single base returns only the first element).
   */
  _readDOMValue: function () {
    return {value: this._selectedValues};
  },

  /**
   * Enforce the "All" (`''`) mutual-exclusion, mirroring legacy `multiselect-filter._onSelectChange`:
   * selecting "All" clears every concrete value; selecting a concrete value clears "All"; an empty
   * selection falls back to "All" (`['']`).
   *
   * @param {string[]} values the raw new selection from `MultiSelectInput`
   * @return {string[]} the normalised selection
   * @protected
   */
  _applyAllExclusion: function (values: string[]): string[] {
    // At initialization the model value is `''` (meaning "All"); treat it as `['']` for the diff.
    const previousValue = '' === this.getValue().value ? [''] : this.getValue().value;

    // Did the user just add "All" to remove every previous selection?
    const addAll = _.contains(_.difference(values, previousValue), '');

    values = _.contains(values, '') ? _.without(values, '') : values;
    values = _.isEmpty(values) ? [''] : values;
    values = addAll ? [''] : values;

    return values;
  },

  /**
   * {@inheritdoc}
   *
   * Apply the "All" exclusion before storing, then re-render and push the formatted value (same
   * store→render→setValue shape as the single base).
   */
  _onReactChange: function (values: string[]) {
    this._selectedValues = this._applyAllExclusion(values);
    this._renderReact();
    this.setValue(this._formatRawValue(this._readDOMValue()));
  },
});
