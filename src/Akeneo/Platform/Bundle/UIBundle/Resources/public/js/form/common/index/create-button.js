'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

require('jquery');
var _ = __pimInterop(require('underscore'));
var __ = __pimInterop(require('oro/translator'));
var BaseForm = __pimInterop(require('pim/form'));
var template = __pimInterop(require('pim/template/form/index/create-button'));
var Routing = __pimInterop(require('routing'));
var DialogForm = __pimInterop(require('pim/dialogform'));
var FormBuilder = __pimInterop(require('pim/form-builder'));

module.exports = BaseForm.extend({
  template: _.template(template),
  dialog: null,

  /**
   * {@inheritdoc}
   */
  initialize: function (config) {
    this.config = config.config;

    BaseForm.prototype.initialize.apply(this, arguments);
  },

  /**
   * {@inheritdoc}
   */
  render: function () {
    this.$el.html(
      this.template({
        title: __(this.config.title),
        iconName: this.config.iconName,
        url: this.config.url ? Routing.generate(this.config.url) : '',
      })
    );

    if (this.config.modalForm) {
      this.$el.on(
        'click',
        function () {
          FormBuilder.build(this.config.modalForm).then(function (modal) {
            modal.open();
          });
        }.bind(this)
      );

      return this;
    }

    // TODO-Remove the following line when all entities will be managed (TIP-730 completed)
    this.dialog = new DialogForm('#create-button-extension');

    return this;
  },
});
