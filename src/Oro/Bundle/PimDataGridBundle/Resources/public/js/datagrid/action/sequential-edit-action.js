function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var $ = __pimInterop(require('jquery'));
var _ = __pimInterop(require('underscore'));
var __ = __pimInterop(require('oro/translator'));
var Routing = __pimInterop(require('routing'));
var MassAction = __pimInterop(require('oro/datagrid/mass-action'));
var router = __pimInterop(require('pim/router'));
var messenger = __pimInterop(require('oro/messenger'));
var sequentialEditProvider = __pimInterop(require('pim/provider/sequential-edit-provider'));
var LoadingMask = __pimInterop(require('oro/loading-mask'));
('use strict');

module.exports = MassAction.extend({
  /**
   * Execute sequential edit
   */
  execute: function () {
    const params = Object.assign({}, this.getActionParameters(), {
      gridName: this.datagrid.name,
      actionName: 'sequential_edit',
    });

    const loadingMask = new LoadingMask();
    loadingMask.render().$el.appendTo($('.hash-loading-mask')).show();

    return $.ajax({
      url: Routing.generate('pim_enrich_sequential_edit_rest_get_ids'),
      method: 'POST',
      data: params,
    })
      .then(response => {
        sequentialEditProvider.set(response.entities);

        if (1000 < response.total) {
          messenger.notify(
            'warning',
            __('pim_enrich.entity.product.module.sequential_edit.item_limit', {count: response.total})
          );
        }

        if (0 === response.total) {
          messenger.notify('error', __('pim_enrich.entity.product.module.sequential_edit.empty'));

          return;
        }

        const entity = _.first(response.entities);
        if (entity.type === 'product') {
          router.redirectToRoute('pim_enrich_product_edit', {uuid: entity.id});
        } else {
          router.redirectToRoute('pim_enrich_' + entity.type + '_edit', {id: entity.id});
        }
      })
      .always(() => {
        loadingMask.hide().$el.remove();
      });
  },
});
