import $ from 'jquery';
import _ from 'underscore';
import 'oro/mediator';
import BaseForm from 'pim/form';
import template from 'pim/template/product/meta/family';
import FetcherRegistry from 'pim/fetcher-registry';
import UserContext from 'pim/user-context';
import * as i18n from 'pim/i18n';

export default BaseForm.extend({
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
