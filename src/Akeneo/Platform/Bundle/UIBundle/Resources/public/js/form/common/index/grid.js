'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var BaseForm = __pimInterop(require('pim/form'));
var Grid = __pimInterop(require('pim/common/grid'));
var UserContext = __pimInterop(require('pim/user-context'));

module.exports = BaseForm.extend({
  grid: null,

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
  configure: function () {
    var metaData = this.config.metadata || {};
    metaData[this.config.localeKey || 'localeCode'] = UserContext.get('catalogLocale');

    this.grid = new Grid(this.config.alias, metaData);

    BaseForm.prototype.configure.apply(this, arguments);
  },

  /**
   * {@inheritdoc}
   */
  render: function () {
    this.$el.empty().append(this.grid.render().$el);

    return this;
  },
});
