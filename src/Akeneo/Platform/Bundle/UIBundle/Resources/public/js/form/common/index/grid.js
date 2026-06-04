import BaseForm from 'pim/form';
import Grid from 'pim/common/grid';
import UserContext from 'pim/user-context';

export default BaseForm.extend({
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
