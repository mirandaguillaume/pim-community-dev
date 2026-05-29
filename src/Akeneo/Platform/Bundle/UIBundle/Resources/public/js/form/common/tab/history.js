'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var _ = __pimInterop(require('underscore'));
var BaseForm = __pimInterop(require('pim/form'));
var Grid = __pimInterop(require('pim/common/grid'));
var __ = __pimInterop(require('oro/translator'));

module.exports = BaseForm.extend({
  className: 'tabbable history',
  historyGrid: null,

  /**
   * @param {Object} meta
   */
  initialize: function (meta) {
    this.config = _.extend({}, meta.config);
    this.config.modelDependent = false;

    return BaseForm.prototype.initialize.call(this, arguments);
  },

  /**
   * {@inheritdoc}
   */
  configure: function () {
    this.trigger('tab:register', {
      code: this.config.tabCode ? this.config.tabCode : this.code,
      label: __(this.config.title),
    });

    return BaseForm.prototype.configure.apply(this, arguments);
  },

  /**
   * {@inheritdoc}
   */
  render: function () {
    if (!this.historyGrid) {
      this.historyGrid = new Grid('history-grid', {
        object_class: this.config.class,
        object_id: this.getFormData().meta.id,
      });
    }

    this.$el.empty().append(this.historyGrid.render().$el);

    return this;
  },
});
