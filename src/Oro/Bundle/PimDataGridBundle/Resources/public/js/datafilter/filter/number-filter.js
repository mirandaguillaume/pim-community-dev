function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var ChoiceFilter = __pimInterop(require('oro/datafilter/choice-filter'));
('use strict');

module.exports = ChoiceFilter.extend({
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
