import 'underscore';
import BaseForm from 'pim/form';
import mediator from 'oro/mediator';
import FiltersManager from 'oro/datafilter/collection-filters-manager';

export default BaseForm.extend({
  displayAsPanel: false,
  isLoaded: false,

  /**
   * @inheritdoc
   */
  initialize(meta) {
    this.displayAsPanel = undefined === meta.config.displayAsPanel ? false : meta.config.displayAsPanel;

    this.listenTo(mediator, 'datagrid_filters:loaded', this.showFilterManager.bind(this));

    BaseForm.prototype.initialize.apply(this, arguments);
  },

  /**
   * Creates a new FiltersManager and renders it
   *
   * @param {Object} options
   */
  showFilterManager(options) {
    options.displayAsPanel = this.displayAsPanel;
    if (this.isLoaded) {
      return;
    }
    this.isLoaded = true;

    const filtersList = new FiltersManager(options);

    this.$el.append(filtersList.render().$el);

    mediator.trigger('datagrid_filters:build.post', filtersList);
  },
});
