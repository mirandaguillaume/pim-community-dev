import __ from 'oro/translator';
import BaseForm from 'pim/form';
import 'pim/fetcher-registry';
import UserContext from 'pim/user-context';
import Grid from 'pim/common/grid';

export default BaseForm.extend({
  className: 'products',

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
    this.trigger('tab:register', {
      code: this.code,
      label: __(this.config.label),
    });

    return BaseForm.prototype.configure.apply(this, arguments);
  },

  /**
   * {@inheritdoc}
   */
  render: function () {
    if (!this.productGroupGrid) {
      this.productGroupGrid = new Grid(this.config.gridId, {
        locale: UserContext.get('catalogLocale'),
        currentGroup: this.getFormData().meta.id,
        id: this.getFormData().meta.id,
        selection: this.getFormData().products,
        selectionIdentifier: 'id',
      });

      this.productGroupGrid.on(
        'grid:selection:updated',
        function (selection) {
          this.setData('products', selection);
        }.bind(this)
      );

      this.getRoot().on('pim_enrich:form:entity:post_fetch', () => {
        const shouldRefresh = this.code === this.getParent().getCurrentTab();
        if (shouldRefresh) this.productGroupGrid.refresh();
      });
    }

    this.$el.empty().append(this.productGroupGrid.render().$el);

    this.renderExtensions();
  },
});
