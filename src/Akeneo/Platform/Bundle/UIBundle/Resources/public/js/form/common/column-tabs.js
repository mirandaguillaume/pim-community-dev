import 'jquery';
import _ from 'underscore';
import FormTabs from 'pim/form/common/form-tabs';
import template from 'pim/template/form/column-tabs';

export default FormTabs.extend({
  className: '',

  template: _.template(template),

  currentKey: 'current_column_tab',

  /**
   * {@inheritdoc}
   */
  configure: function () {
    this.listenTo(this.getRoot(), 'column-tab:select-tab', this.selectTab);

    return FormTabs.prototype.configure.apply(this, arguments);
  },

  /**
   * {@inheritdoc}
   */
  registerTab: function (event) {
    FormTabs.prototype.registerTab.apply(this, arguments);
    this.getRoot().trigger('column-tab:register', event);
  },
});
