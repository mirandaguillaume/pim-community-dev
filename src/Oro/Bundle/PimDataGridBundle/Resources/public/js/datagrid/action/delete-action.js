import _ from 'underscore';
import * as messenger from 'oro/messenger';
import __ from 'oro/translator';
import 'pim/dialog';
import ModelAction from 'oro/datagrid/model-action';
import mediator from 'oro/mediator';
import userContext from 'pim/user-context';
import DeleteConfirm from 'oro/datagrid/delete-confirm';
import FetcherRegistry from 'pim/fetcher-registry';

export default ModelAction.extend({
  errorModal: undefined,

  confirmModal: undefined,

  /** @property {Boolean} */
  noHref: false,

  /**
   * Initialize view
   *
   * @param {Object} options
   * @param {Backbone.Model} options.model Optional parameter
   * @throws {TypeError} If model is undefined
   */
  initialize: function (options) {
    options = options || {};

    this.gridName = options.datagrid.name;

    ModelAction.prototype.initialize.apply(this, arguments);
  },

  /**
   * Execute delete model
   */
  execute: function () {
    this.getConfirmDialog();
  },

  /**
   * Confirm delete item
   */
  doDelete: function () {
    this.model.id = true;
    this.model.destroy({
      url: this.getLink(),
      wait: true,
      error: function (element, response) {
        let contentType = response.getResponseHeader('content-type');
        let message = '';
        //Need to check if it is a json because the backend can return an error
        if (contentType.indexOf('application/json') !== -1) {
          const decodedResponse = JSON.parse(response.responseText);
          if (undefined !== decodedResponse.message) {
            message = decodedResponse.message;
          }
        }

        this.showErrorFlashMessage(message);
      }.bind(this),
      success: function () {
        var messageText = __('pim_enrich.entity.' + this.getEntityCode() + '.flash.delete.success');
        messenger.notify('success', messageText);
        userContext.initialize();

        mediator.trigger('grid_action_execute:product-grid:delete');
        mediator.trigger('datagrid:doRefresh:' + this.gridName);
        if (this.gridName === 'association-type-grid') {
          FetcherRegistry.getFetcher('association-type').clear();
        }
      }.bind(this),
    });
  },

  /**
   * Get view for confirm modal
   */
  getConfirmDialog: function () {
    this.confirmModal = DeleteConfirm.getConfirmDialog(
      this.getEntityCode(),
      this.doDelete.bind(this),
      this.getEntityHint(true)
    );

    return this.confirmModal;
  },

  /**
   * Get view for error modal
   *
   * @return {oro.Modal}
   */
  showErrorFlashMessage: function (response) {
    let message = '';

    if (typeof response === 'string' && response !== '') {
      message = response;
    } else {
      try {
        message = JSON.parse(response).message;
      } catch (e) {
        message = __('pim_enrich.entity.' + this.getEntityHint() + '.flash.delete.fail');
      }
    }

    messenger.notify('error', '' === message ? __('error.removing.' + this.getEntityHint()) : message);
  },

  /**
   * Creates launcher
   *
   * @param {Object} options Launcher options
   * @return {oro.datagrid.ActionLauncher}
   */
  createLauncher: function (options) {
    this.launcherOptions = _.extend({noHref: this.noHref}, this.launcherOptions);

    return ModelAction.prototype.createLauncher.apply(this, arguments);
  },
});
