import $ from 'jquery';
import _ from 'underscore';
import __ from 'oro/translator';
import Routing from 'routing';
import MassAction from 'oro/datagrid/mass-action';
import router from 'pim/router';
import * as messenger from 'oro/messenger';
import sequentialEditProvider from 'pim/provider/sequential-edit-provider';
import LoadingMask from 'oro/loading-mask';

export default MassAction.extend({
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
