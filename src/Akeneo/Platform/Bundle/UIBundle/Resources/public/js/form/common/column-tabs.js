'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

require('jquery');
var _ = __pimInterop(require('underscore'));
var FormTabs = __pimInterop(require('pim/form/common/form-tabs'));
var template = __pimInterop(require('pim/template/form/column-tabs'));

module.exports = FormTabs.extend({
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
