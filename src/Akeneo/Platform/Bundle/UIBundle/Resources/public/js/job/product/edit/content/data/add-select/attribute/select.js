import $ from 'jquery';
import 'underscore';
import AddAttributeSelect from 'pim/product/add-select/attribute';

export default AddAttributeSelect.extend({
  /**
   * {@inheritdoc}
   */
  getItemsToExclude: function () {
    return $.Deferred().resolve(this.getParent().getCurrentFilters());
  },
});
