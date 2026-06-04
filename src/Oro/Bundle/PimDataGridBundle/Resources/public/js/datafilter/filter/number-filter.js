import ChoiceFilter from 'oro/datafilter/choice-filter';

export default ChoiceFilter.extend({
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
