'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var _ = __pimInterop(require('underscore'));
require('oro/translator');
var BaseForm = __pimInterop(require('pim/form'));
var template = __pimInterop(require('pim/template/family/tab/attributes/toolbar'));

module.exports = BaseForm.extend({
  className: 'AknGridToolbar',
  template: _.template(template),
  readOnly: false,

  /**
   * {@inheritdoc}
   */
  initialize: function (config) {
    this.config = config.config;

    BaseForm.prototype.initialize.apply(this, arguments);
  },

  /**
   * {@inheritdoc}
   */
  configure: function () {
    this.listenTo(
      this.getRoot(),
      'pim_enrich:form:update_read_only',
      function (readOnly) {
        this.readOnly = readOnly;

        this.render();
      }.bind(this)
    );

    BaseForm.prototype.configure.apply(this, arguments);
  },

  /**
   * {@inheritdoc}
   */
  render: function () {
    if (!this.configured) {
      return this;
    }

    if (this.readOnly) {
      this.$el.empty();

      return this;
    }

    this.$el.html(this.template({}));

    this.renderExtensions();
  },
});
