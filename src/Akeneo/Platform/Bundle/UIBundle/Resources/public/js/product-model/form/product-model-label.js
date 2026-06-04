import Label from 'pim/form/common/label';
import UserContext from 'pim/user-context';

export default Label.extend({
  /**
   * Provide the object label
   * @return {String}
   */
  getLabel: function () {
    var meta = this.getFormData().meta;

    if (meta && meta.label) {
      return meta.label[UserContext.get('catalogLocale')];
    }

    return this.getFormData().identifier;
  },
});
