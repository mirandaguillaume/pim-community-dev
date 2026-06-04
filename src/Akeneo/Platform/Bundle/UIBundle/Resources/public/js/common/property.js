export default {
  /**
   * Access a property in an object
   *
   * @param {object} data
   * @param {string} path
   * @param {*}  defaultValue
   *
   * @return {*}
   */
  accessProperty: function (data, path, defaultValue) {
    defaultValue = defaultValue || null;
    const pathPart = path.split('.');

    const part = pathPart[0].replace(/__DOT__/g, '.');
    if (undefined === data[part]) {
      return defaultValue;
    }

    return 1 === pathPart.length
      ? data[part]
      : this.accessProperty(data[part], pathPart.slice(1).join('.'), defaultValue);
  },

  /**
   * Update a property in an object
   *
   * @param {object} data
   * @param {string} path
   * @param {*}  value
   *
   * @return {*}
   */
  updateProperty: function (data, path, value) {
    var pathPart = path.split('.');

    const part = pathPart[0].replace(/__DOT__/g, '.');
    data[part] = 1 === pathPart.length ? value : this.updateProperty(data[part], pathPart.slice(1).join('.'), value);

    return data;
  },

  /**
   * Create a safe path by concatenating escaped path segments to avoid dots of being incorrectly interpreted
   *
   * @param Array path
   *
   * @returns String
   */
  propertyPath: function (path) {
    return path.map(e => e.replace(/\./g, '__DOT__')).join('.');
  },
};
