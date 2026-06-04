import ModelAction from 'oro/datagrid/model-action';

export default ModelAction.extend({
  /** @property {Boolean} */
  noHref: false,

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
