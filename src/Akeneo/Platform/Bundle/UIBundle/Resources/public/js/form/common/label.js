'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var BaseForm = __pimInterop(require('pim/form'));
var UserContext = __pimInterop(require('pim/user-context'));
var i18n = __pimInterop(require('pim/i18n'));
var _ = __pimInterop(require('underscore'));

module.exports = BaseForm.extend({
  tagName: 'h1',
  className: 'AknTitleContainer-title',

  /**
   * @param {Object} meta
   */
  initialize: function (meta) {
    this.config = _.extend({}, meta.config);
  },

  /**
   * {@inheritdoc}
   */
  configure: function () {
    UserContext.off('change:catalogLocale change:catalogScope', this.render);
    this.listenTo(UserContext, 'change:catalogLocale', this.render);
    this.listenTo(UserContext, 'change:catalogScope', this.render);
    this.listenTo(this.getRoot(), 'pim_enrich:form:entity:post_update', this.render);

    return BaseForm.prototype.configure.apply(this, arguments);
  },

  /**
   * {@inheritdoc}
   */
  render: function () {
    this.$el.text(this.getLabel());

    return this;
  },

  /**
   * Provide the object label
   *
   * @return {String}
   */
  getLabel: function () {
    var data = this.getFormData();

    if (this.config.field) {
      return data[this.config.field];
    }

    if (undefined === data.labels) {
      return '';
    }

    return i18n.getLabel(data.labels, UserContext.get('catalogLocale'), data.code);
  },
});
