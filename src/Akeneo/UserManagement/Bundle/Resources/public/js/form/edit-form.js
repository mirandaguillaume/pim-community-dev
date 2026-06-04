import BaseEditForm from 'pim/form/common/edit-form';
import UserContext from 'pim/user-context';

export default BaseEditForm.extend({
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
