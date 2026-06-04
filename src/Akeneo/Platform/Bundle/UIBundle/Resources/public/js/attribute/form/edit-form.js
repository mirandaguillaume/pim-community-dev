import BaseEditForm from 'pim/form/common/edit-form';

export default BaseEditForm.extend({
  type: null,

  /**
   * {@inheritdoc}
   */
  configure: function () {
    this.on('pim_enrich:form:entity:post_fetch', this.render);

    return BaseEditForm.prototype.configure.apply(this, arguments);
  },

  render: function () {
    this.$el.addClass('attribute');

    return BaseEditForm.prototype.render.apply(this, arguments);
  },

  /**
   * Sets the attribute code for dynamic tree building purpose at configuration time.
   *
   * @param {String} type
   */
  setCode: function (code) {
    this.code = code;
  },

  /**
   * Sets the attribute type for dynamic tree building purpose at configuration time.
   *
   * @param {String} type
   */
  setType: function (type) {
    this.type = type;
  },

  /**
   * Returns the view name associated to the key.
   *
   * @return {String}
   */
  getType: function () {
    return this.type;
  },

  setLabels: function (labels) {
    this.labels = labels;
  },
  setLocalizable: function (localizable) {
    this.localizable = localizable === 'true';
  },
  setScopable: function (scopable) {
    this.scopable = scopable === 'true';
  },
  setUnique: function (unique) {
    this.unique = unique === 'true';
  },
});
