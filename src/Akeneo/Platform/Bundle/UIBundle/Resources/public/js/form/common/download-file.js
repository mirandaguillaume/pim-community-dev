'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var _ = __pimInterop(require('underscore'));
var __ = __pimInterop(require('oro/translator'));
var BaseForm = __pimInterop(require('pim/form'));
var template = __pimInterop(require('pim/template/form/download-file'));
var Routing = __pimInterop(require('routing'));
require('pim/user-context');
var propertyAccessor = __pimInterop(require('pim/common/property'));

module.exports = BaseForm.extend({
  template: _.template(template),

  /**
   * {@inheritdoc}
   */
  initialize: function (meta) {
    this.config = meta.config;

    BaseForm.prototype.initialize.apply(this, arguments);
  },

  /**
   * {@inheritdoc}
   */
  configure: function () {
    this.listenTo(this.getRoot(), 'pim_enrich:form:entity:post_update', this.render);

    return BaseForm.prototype.configure.apply(this, arguments);
  },

  /**
   * {@inheritdoc}
   */
  render: function () {
    if (!this.isVisible()) {
      return this;
    }
    this.$el.html(
      this.template({
        btnLabel: __(this.config.label),
        btnIcon: this.config.iconName,
        url: this.getUrl(),
      })
    );

    return this;
  },

  /**
   * Get the url with parameters
   *
   * @returns {string}
   */
  getUrl: function () {
    var parameters = {};
    if (this.config.urlParams) {
      var formData = this.getFormData();
      this.config.urlParams.forEach(function (urlParam) {
        parameters[urlParam.property] = propertyAccessor.accessProperty(formData, urlParam.path);
      });
    }

    return Routing.generate(this.config.url, parameters);
  },

  /**
   * Returns true if the extension should be visible
   *
   * @returns {boolean}
   */
  isVisible: function () {
    return propertyAccessor.accessProperty(this.getFormData(), this.config.isVisiblePath);
  },
});
