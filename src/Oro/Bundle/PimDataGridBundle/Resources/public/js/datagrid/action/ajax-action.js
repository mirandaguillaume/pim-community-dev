function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var ModelAction = __pimInterop(require('oro/datagrid/model-action'));
('use strict');

module.exports = ModelAction.extend({
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
