import 'jquery';
import 'underscore';
import 'backbone';
import BaseForm from 'pim/form';

export default BaseForm.extend({
  /**
   * {@inheritdoc}
   */
  configure: function () {
    this.listenTo(this.getRoot(), 'pim_enrich:form:filter:extension:add', this.addFilterExtension.bind(this));

    return BaseForm.prototype.configure.apply(this, arguments);
  },

  /**
   * Sets filters in readonly mode.
   *
   * @param {Object} event
   */
  addFilterExtension: function (event) {
    event.filter.setEditable(false);
  },
});
