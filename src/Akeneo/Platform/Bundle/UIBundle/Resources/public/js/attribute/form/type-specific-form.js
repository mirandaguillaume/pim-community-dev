/**
 * Special view that serves as a bridge between its parent and another tree.
 * It builds a tree on-the-fly at configure time then adds it to its own children. The result is a fully functional
 * tree as if it was build "statically".
 * The goal is to build modular view trees without duplicating a bunch of conf.
 *
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

require('jquery');
require('underscore');
require('backbone');
require('oro/translator');
var BaseForm = __pimInterop(require('pim/form'));
var FormBuilder = __pimInterop(require('pim/form-builder'));
var FormRegistry = __pimInterop(require('pim/attribute-edit-form/type-specific-form-registry'));

module.exports = BaseForm.extend({
  config: {},

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
    var formName = FormRegistry.getFormName(this.getRoot().getType(), this.config.mode);

    if (undefined !== formName && null !== formName) {
      return FormBuilder.getFormMeta(formName)
        .then(FormBuilder.buildForm)
        .then(
          function (form) {
            this.addExtension(form.code, form, 'self', 100);

            return BaseForm.prototype.configure.apply(this);
          }.bind(this)
        );
    }

    return BaseForm.prototype.configure.apply(this);
  },
});
