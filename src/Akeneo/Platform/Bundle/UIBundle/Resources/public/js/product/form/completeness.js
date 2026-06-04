import 'jquery';
import _ from 'underscore';
import __ from 'oro/translator';
import BaseForm from 'pim/form';
import template from 'pim/template/product/completeness';
import 'pim/fetcher-registry';
import 'pim/i18n';
import UserContext from 'pim/user-context';
import {ChannelsLocalesCompleteness, formatProductCompleteness} from '@akeneo-pim-community/enrichment';

export default BaseForm.extend({
  template: _.template(template),
  className: 'panel-pane completeness-panel AknCompletenessPanel',
  initialFamily: null,

  /**
   * {@inheritdoc}
   */
  initialize: function () {
    this.initialFamily = null;

    BaseForm.prototype.initialize.apply(this, arguments);
  },

  /**
   * {@inheritdoc}
   */
  configure: function () {
    this.trigger('tab:register', {
      code: this.code,
      label: __('pim_enrich.entity.product.module.completeness.title'),
    });

    this.listenTo(this.getRoot(), 'pim_enrich:form:entity:post_fetch', this.render);
    this.listenTo(UserContext, 'change:catalogLocale', this.render);

    return BaseForm.prototype.configure.apply(this, arguments);
  },

  /**
   * {@inheritdoc}
   */
  render: function () {
    if (!this.configured || this.code !== this.getParent().getCurrentTab()) {
      return this;
    }

    if (this.getFormData().meta) {
      const catalogLocale = UserContext.get('catalogLocale');
      const sortedCompleteness = this.sortCompleteness(this.getFormData().meta.completenesses);
      const channelsLocalesRatios = formatProductCompleteness(sortedCompleteness, catalogLocale);

      this.renderReact(ChannelsLocalesCompleteness, {channelsLocalesRatios}, this.el);
    }

    return this;
  },

  /**
   * Sort completenesses. Put the user current catalog scope first.
   *
   * @param completenesses
   *
   * @returns {Array}
   */
  sortCompleteness: function (completenesses) {
    if (_.isEmpty(completenesses)) {
      return [];
    }
    var sortedCompleteness = [_.findWhere(completenesses, {channel: UserContext.get('catalogScope')})];

    return _.union(sortedCompleteness, completenesses);
  },

  /**
   * On family change listener
   */
  onChangeFamily: function () {
    if (!_.isEmpty(this.getRoot().model._previousAttributes)) {
      var data = this.getFormData();
      data.meta.completenesses = [];
      this.setData(data);

      this.render();
    }
  },
});
