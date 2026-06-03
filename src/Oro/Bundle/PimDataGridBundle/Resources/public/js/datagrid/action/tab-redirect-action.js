function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var NavigateAction = __pimInterop(require('oro/datagrid/navigate-action'));
('use strict');

/**
 * Redirects to a specific tab
 *
 * @author  Antoine Guigan <antoine@akeneo.com>
 * @class   Pim.Datagrid.Action.TabRedirectAction
 * @export  pim/datagrid/tab-redirect-action
 * @extends oro.datagrid.AbstractAction
 */
var parent = NavigateAction.prototype,
  TabRedirectAction = NavigateAction.extend({
    useDirectLauncherLink: false,
    run: function () {
      sessionStorage.redirectTab = '#' + this.tab;
      parent.run.call(this);
    },
  });
module.exports = TabRedirectAction;
