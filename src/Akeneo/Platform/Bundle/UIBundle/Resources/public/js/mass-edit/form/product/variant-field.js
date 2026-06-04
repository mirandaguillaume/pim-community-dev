import Variant from 'pim/product-model/form/creation/variant';

export default Variant.extend({
  readOnly: false,

  /**
   * {@inheritdoc}
   */
  initialize() {
    Variant.prototype.initialize.apply(this, arguments);

    this.readOnly = false;
  },

  /**
   * {@inheritdoc}
   */
  configure() {
    this.listenTo(this, 'mass-edit:update-read-only', this.setReadOnly.bind(this));

    return Variant.prototype.configure.apply(this, arguments);
  },

  /**
   * {@inheritdoc}
   */
  isReadOnly() {
    return this.readOnly || !this.getFormData().family;
  },

  /**
   * Updates the readOnly parameter to avoid edition of the field
   *
   * @param {Boolean} readOnly
   */
  setReadOnly(readOnly) {
    this.readOnly = readOnly;
  },
});
