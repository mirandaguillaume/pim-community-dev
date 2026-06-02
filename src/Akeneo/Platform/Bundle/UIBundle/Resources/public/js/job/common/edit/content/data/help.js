/**
 * Extension to add a help tooltip on filters.
 *
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var $ = __pimInterop(require('jquery'));
var _ = __pimInterop(require('underscore'));
var __ = __pimInterop(require('oro/translator'));
var BaseForm = __pimInterop(require('pim/form'));
var template = __pimInterop(require('pim/template/export/product/edit/content/data/help'));

module.exports = BaseForm.extend({
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
