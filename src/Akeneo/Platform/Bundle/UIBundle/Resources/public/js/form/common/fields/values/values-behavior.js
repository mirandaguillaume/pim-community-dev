export default {
  /**
   * Formats the value according to the standard format then store it by calling the original field's method.
   *
   * @param {Object} BaseField
   * @param {*} value
   */
  writeValue(BaseField, value) {
    BaseField.prototype.updateModel.call(this, [{scope: null, locale: null, data: value}]);
  },

  /**
   * Read a standard formatted value and returns its data.
   *
   * @param {Object} BaseField
   *
   * @returns {*}
   */
  readValue(BaseField) {
    const standardValues = BaseField.prototype.getModelValue.call(this);

    return undefined === standardValues ? undefined : standardValues[0].data;
  },
};
