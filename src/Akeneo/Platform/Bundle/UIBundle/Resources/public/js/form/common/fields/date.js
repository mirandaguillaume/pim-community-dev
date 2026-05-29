/**
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var $ = __pimInterop(require('jquery'));
var _ = __pimInterop(require('underscore'));
var BaseField = __pimInterop(require('pim/form/common/fields/field'));
var Datepicker = __pimInterop(require('datepicker'));
var DateFormatter = __pimInterop(require('pim/formatter/date'));
var DateContext = __pimInterop(require('pim/date-context'));
var template = __pimInterop(require('pim/template/form/common/fields/date'));

module.exports = BaseField.extend({
  events: {
    'change input': function (event) {
      this.errors = [];
      this.updateModel(this.getFieldValue(event.target));
      this.getRoot().render();
    },
  },
  template: _.template(template),
  modelDateFormat: 'yyyy-MM-dd',

  /**
   * {@inheritdoc}
   */
  renderInput: function (templateContext) {
    var value = DateFormatter.format(this.getModelValue(), this.modelDateFormat, DateContext.get('date').format);

    return this.template(
      _.extend(templateContext, {
        value,
        readOnly: this.readOnly,
      })
    );
  },

  /**
   * {@inheritdoc}
   */
  postRender: function () {
    Datepicker.init(this.$('.date-wrapper'), {
      format: DateContext.get('date').format,
      defaultFormat: DateContext.get('date').defaultFormat,
      language: DateContext.get('language'),
    }).on(
      'changeDate',
      function () {
        this.errors = [];
        this.updateModel(this.getFieldValue(this.$('input')[0]));
        this.$('.date-wrapper').datetimepicker('destroy');
        this.getRoot().render();
      }.bind(this)
    );
  },

  /**
   * {@inheritdoc}
   */
  getFieldValue: function (field) {
    var dateFormat = DateContext.get('date').format;
    var value = $(field).val();

    return DateFormatter.format(value, dateFormat, this.modelDateFormat);
  },
});
