/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var BaseEditForm = __pimInterop(require('pim/form/common/edit-form'));
var UserContext = __pimInterop(require('pim/user-context'));

module.exports = BaseEditForm.extend({
  /**
   * {@inheritdoc}
   */
  configure: function () {
    this.on('pim_enrich:form:entity:post_fetch', this._refreshUserContext);

    return BaseEditForm.prototype.configure.apply(this, arguments);
  },

  _refreshUserContext: function () {
    UserContext.refresh();
  },
});
