import SimpleSelectAsync from 'pim/form/common/fields/simple-select-async';

export default SimpleSelectAsync.extend({
  readOnly: false,

  /**
   * {@inheritdoc}
   */
  initialize() {
    SimpleSelectAsync.prototype.initialize.apply(this, arguments);

    this.readOnly = false;
  },

  /**
   * {@inheritdoc}
   */
  configure() {
    this.listenTo(this, 'mass-edit:update-read-only', this.setReadOnly.bind(this));

    return SimpleSelectAsync.prototype.configure.apply(this, arguments);
  },

  /**
   * {@inheritdoc}
   */
  isReadOnly() {
    return this.readOnly || SimpleSelectAsync.prototype.isReadOnly.apply(this, arguments);
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
