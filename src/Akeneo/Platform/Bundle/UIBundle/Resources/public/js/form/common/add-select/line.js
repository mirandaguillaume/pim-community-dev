'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

require('jquery');
var _ = __pimInterop(require('underscore'));
var Backbone = __pimInterop(require('backbone'));
var template = __pimInterop(require('pim/template/form/add-select/line'));

module.exports = Backbone.View.extend({
  className: 'select2-results',
  template: _.template(template),
  checked: false,
  item: null,

  /**
   * {@inheritdoc}
   */
  initialize: function () {
    this.item = this.options.item;
  },

  /**
   * {@inheritdoc}
   */
  render: function () {
    this.$el.html(
      this.template({
        item: this.item,
        checked: this.checked,
      })
    );

    return this;
  },

  /**
   * Update the checkbox status then render the view
   *
   * @param {bool} checked
   */
  setCheckedCheckbox: function (checked) {
    this.checked = checked;

    this.render();
  },
});
