import $ from 'jquery';
import _ from 'underscore';
import __ from 'oro/translator';
import BaseForm from 'pim/form';
import template from 'pim/template/export/product/edit/content/data/help';

export default BaseForm.extend({
  template: _.template(template),

  /**
   * {@inheritdoc}
   */
  configure: function () {
    this.listenTo(this.getRoot(), 'pim_enrich:form:filter:extension:add', this.addFilterExtension.bind(this));

    return BaseForm.prototype.configure.apply(this, arguments);
  },

  /**
   * Adds the extension to filters.
   * If the translation is not here the tooltip won't be displayed at all.
   *
   * @param {Object} event
   */
  addFilterExtension: function (event) {
    var key = 'pim_enrich.export.product.filter.' + event.filter.shortname + '.help';
    var text = __(key);

    if (key === text) {
      return;
    }

    var $content = $(this.template({text: text}));

    $content.find('[data-toggle="tooltip"]').tooltip();

    event.filter.addElement('after-input', 'help', $content);
  },
});
