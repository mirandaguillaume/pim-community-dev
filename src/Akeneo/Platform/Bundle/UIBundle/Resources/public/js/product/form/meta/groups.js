'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var $ = __pimInterop(require('jquery'));
var _ = __pimInterop(require('underscore'));
var __ = __pimInterop(require('oro/translator'));
require('oro/mediator');
var Backbone = __pimInterop(require('backbone'));
var BaseForm = __pimInterop(require('pim/form'));
var Routing = __pimInterop(require('routing'));
var formTemplate = __pimInterop(require('pim/template/product/meta/groups'));
var modalTemplate = __pimInterop(require('pim/template/product/meta/group-modal'));
var UserContext = __pimInterop(require('pim/user-context'));
var FetcherRegistry = __pimInterop(require('pim/fetcher-registry'));
var GroupManager = __pimInterop(require('pim/group-manager'));
var router = __pimInterop(require('pim/router'));
var i18n = __pimInterop(require('pim/i18n'));
var LoadingMask = __pimInterop(require('oro/loading-mask'));
require('bootstrap-modal');

module.exports = BaseForm.extend({
  tagName: 'span',

  className: 'AknColumn-block product-groups',

  template: _.template(formTemplate),

  modalTemplate: _.template(modalTemplate),

  events: {
    'click .product-group': 'displayModal',
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
    if (!this.configured) {
      return this;
    }

    GroupManager.getProductGroups(this.getFormData()).done(
      function (groups) {
        groups = this.prepareGroupsForTemplate(groups);

        if (groups.length) {
          this.$el.html(
            this.template({
              label: __('pim_enrich.entity.product.module.meta.groups'),
              groups: groups,
            })
          );
        } else {
          this.$el.html('');
        }
      }.bind(this)
    );

    return this;
  },

  /**
   * Prepare groups for being displayed in the template
   *
   * @param {Array} groups
   *
   * @returns {Array}
   */
  prepareGroupsForTemplate: function (groups) {
    var locale = UserContext.get('catalogLocale');

    return _.map(groups, function (group) {
      return {
        label: group.labels[locale] || '[' + group.code + ']',
        code: group.code,
        isVariant: 'VARIANT' === group.type,
      };
    });
  },

  /**
   * Get the product list for the given group
   *
   * @param {integer} groupCode
   *
   * @returns {Array}
   */
  getProductList: function (groupCode) {
    return $.getJSON(Routing.generate('pim_enrich_group_rest_list_products', {identifier: groupCode})).then(_.identity);
  },

  /**
   * Show the modal which displays infos about produt groups
   *
   * @param {Object} event
   */
  displayModal: function (event) {
    var loadingMask = new LoadingMask();
    loadingMask.render().$el.appendTo(this.getRoot().$el).show();

    GroupManager.getProductGroups(this.getFormData()).done(
      function (groups) {
        var group = _.findWhere(groups, {code: event.currentTarget.dataset.group});

        $.when(this.getProductList(group.code), FetcherRegistry.getFetcher('attribute').getIdentifierAttribute()).done(
          function (productList, identifierAttribute) {
            loadingMask.remove();
            this.groupModal = new Backbone.BootstrapModal({
              okText: __('pim_enrich.entity.product.module.show_group.view_group'),
              cancelText: __('pim_common.cancel'),
              title: __('pim_enrich.entity.product.module.show_group.title', {
                group: i18n.getLabel(group.labels, UserContext.get('catalogLocale'), group.code),
              }),
              content: this.modalTemplate({
                products: productList.products,
                productCount: productList.productCount,
                identifier: identifierAttribute,
                locale: UserContext.get('catalogLocale'),
              }),
              illustrationClass: 'group',
            });

            this.groupModal.on(
              'ok',
              function visitGroup() {
                this.groupModal.close();
                var route = 'pim_enrich_group_edit';
                var parameters = {code: group.code};

                router.redirectToRoute(route, parameters);
              }.bind(this)
            );
            this.groupModal.open();

            this.groupModal.$el.on(
              'click',
              'a[data-product-id]',
              function visitProduct(event) {
                this.groupModal.close();
                router.redirectToRoute('pim_enrich_product_edit', {uuid: event.currentTarget.dataset.productId});
              }.bind(this)
            );
          }.bind(this)
        );
      }.bind(this)
    );
  },
});
