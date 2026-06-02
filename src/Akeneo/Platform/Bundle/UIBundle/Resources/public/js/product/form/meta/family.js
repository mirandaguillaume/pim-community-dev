'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var $ = __pimInterop(require('jquery'));
var _ = __pimInterop(require('underscore'));
require('oro/mediator');
var BaseForm = __pimInterop(require('pim/form'));
var template = __pimInterop(require('pim/template/product/meta/family'));
var FetcherRegistry = __pimInterop(require('pim/fetcher-registry'));
var UserContext = __pimInterop(require('pim/user-context'));
var i18n = __pimInterop(require('pim/i18n'));

module.exports = BaseForm.extend({
  className: 'AknColumn-block',

  template: _.template(template),

  /**
   * {@inheritdoc}
   */
  configure: function () {
    this.listenTo(this.getRoot(), 'pim_enrich:form:entity:post_update', this.render);
    UserContext.off('change:catalogLocale change:catalogScope', this.render);
    this.listenTo(UserContext, 'change:catalogLocale change:catalogScope', this.render);

    return BaseForm.prototype.configure.apply(this, arguments);
  },

  /**
   * {@inheritdoc}
   */
  render: function () {
    if (!this.configured) {
      return this;
    }

    var familyPromise = _.isNull(this.getFormData().family)
      ? $.Deferred().resolve(null)
      : FetcherRegistry.getFetcher('family').fetch(this.getFormData().family);

    familyPromise.then(
      function (family) {
        var product = this.getFormData();

        this.$el.html(
          this.template({
            familyLabel: family
              ? i18n.getLabel(family.labels, UserContext.get('catalogLocale'), product.family)
              : _.__('pim_common.none'),
          })
        );

        BaseForm.prototype.render.apply(this, arguments);
      }.bind(this)
    );
  },
});
