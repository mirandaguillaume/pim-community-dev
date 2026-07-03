import ChoiceFilterReact from 'oro/datafilter/choice-filter-react';

/**
 * React inner-render of the number datagrid filter (C1 Wave 4, Slice C4).
 *
 * Pure config/behaviour subclass of the React `choice-filter-react` base (Slice C3): inherits ALL the
 * React rendering (operator AknDropdown, popup positioning hook, Select2 `'in'` value field) and only
 * re-adds the legacy number override — the NaN guard on apply. Verbatim copy of `number-filter.js`,
 * only the base import differs.
 *
 * Added ALONGSIDE `number-filter.js` (the legacy class stays — `metric`/`price` filters still
 * `NumberFilter.extend` it and are not migrated here); only the `number` FilterTypeRegistry alias is
 * re-pointed to this module.
 */
export default ChoiceFilterReact.extend({
  /**
   * {@inheritdoc}
   */
  _onClickUpdateCriteria: function () {
    const numberValue = Number(this._getInputValue(this.criteriaValueSelectors.value));

    if (isNaN(numberValue)) {
      this._setInputValue(this.criteriaValueSelectors.value, '');
      this._focusCriteria();
    } else {
      this._hideCriteria();
      this.setValue(this._formatRawValue(this._readDOMValue()));
    }
  },
});
