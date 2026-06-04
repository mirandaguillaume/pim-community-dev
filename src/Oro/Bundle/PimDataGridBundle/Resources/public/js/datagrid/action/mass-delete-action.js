import $ from 'jquery';
import 'underscore';
import __ from 'oro/translator';
import Routing from 'routing';
import MassAction from 'oro/datagrid/mass-action';
import router from 'pim/router';
import messenger from 'oro/messenger';
import LoadingMask from 'oro/loading-mask';
import Dialog from 'pim/dialog';

export default MassAction.extend({
  /** @type oro.Modal */
  errorModal: undefined,

  /** @type oro.Modal */
  confirmModal: undefined,

  /** @type {Object} */
  config: undefined,

  initialize: function (options) {
    this.config = __moduleConfig;

    MassAction.prototype.initialize.apply(this, arguments);
  },

  /**
   * Displays a confirm dialog and mass delete if action is confirmed.
   */
  execute: function () {
    this.getData().then(data => {
      this.getConfirmDialog(data);
    });
  },

  /**
   * Converts grid data into pqb filters and gathers job instance code, actions and items count.
   *
   * @return {Promise}
   */
  getData: function () {
    let actionParameters = this.getActionParameters();
    actionParameters.actionName = this.route_parameters['actionName'];
    actionParameters.gridName = this.route_parameters['gridName'];
    const query = `?${$.param(actionParameters)}`;

    return $.ajax({
      url: Routing.generate('pim_enrich_mass_edit_rest_get_filter') + query,
      method: 'POST',
    }).then(response => {
      return {
        filters: response.filters,
        jobInstanceCode: this.config.jobInstanceCode,
        actions: [this.route_parameters['actionName']],
        itemsCount: response.itemsCount,
      };
    });
  },

  /**
   * Get view for confirm modal.
   *
   * @param {Object} data
   *
   * @return {oro.Modal}
   */
  getConfirmDialog: function (data) {
    this.confirmModal = Dialog.confirmDelete(
      __(this.config.confirmLabel),
      __('pim_common.confirm_deletion'),
      this.doMassDelete.bind(this, data),
      this.getEntityHint(true),
      this.config.buttonText
    );

    return this.confirmModal;
  },

  /**
   * Sends request to mass delete items.
   *
   * @param {Object} data
   */
  doMassDelete: function (data) {
    const loadingMask = new LoadingMask();
    loadingMask.render().$el.appendTo($('.hash-loading-mask')).show();

    $.ajax({
      method: 'POST',
      contentType: 'application/json',
      url: Routing.generate(this.config.route),
      data: JSON.stringify(data),
    })
      .then(() => {
        router.redirectToRoute(this.config.backRoute);

        const translatedAction = __('pim_datagrid.mass_action.mass_delete');
        messenger.notify(
          'success',
          __(this.config.launchedLabel, {
            operation: translatedAction,
          })
        );
      })
      .fail(() => {
        messenger.notify('error', __(this.config.launchErrorLabel));
      })
      .always(() => {
        loadingMask.hide().$el.remove();
      });
  },
});
