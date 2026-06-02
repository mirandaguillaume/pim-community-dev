'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

require('jquery');
var _ = __pimInterop(require('underscore'));
var __ = __pimInterop(require('oro/translator'));
var BaseForm = __pimInterop(require('pim/form'));
var template = __pimInterop(require('pim/template/export/common/edit/upload'));

module.exports = BaseForm.extend({
  template: _.template(template),
  events: {
    'change input[type="file"]': 'addFile',
    'click .clear-field': 'removeFile',
  },

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
        file: this.getFormData().file,
        type: this.config.type,
        __,
      })
    );

    this.delegateEvents();

    return this;
  },

  /**
   * When a file is added to the dom input
   */
  addFile: function () {
    var input = this.$('input[type="file"]').get(0);
    if (!input || 0 === input.files.length) {
      return;
    }

    this.setData({file: input.files[0]});

    this.getRoot().trigger('pim_enrich:form:job:file_updated');

    this.render();
  },

  /**
   * When the user remove the file from the input
   */
  removeFile: function () {
    this.setData({file: null});

    this.getRoot().trigger('pim_enrich:form:job:file_updated');

    this.render();
  },
});
