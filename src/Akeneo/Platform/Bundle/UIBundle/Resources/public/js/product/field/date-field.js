'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var $ = __pimInterop(require('jquery'));
var Field = __pimInterop(require('pim/field'));
var _ = __pimInterop(require('underscore'));
var fieldTemplate = __pimInterop(require('pim/template/product/field/date'));
var Datepicker = __pimInterop(require('datepicker'));
var DateContext = __pimInterop(require('pim/date-context'));

module.exports = Field.extend({
  fieldTemplate: _.template(fieldTemplate),
  events: {
    'change .field-input:first input[type="text"]': 'updateModel',
    'click .field-input:first input[type="text"]': 'click',
  },
  datetimepickerOptions: {
    format: DateContext.get('date').format,
    defaultFormat: DateContext.get('date').defaultFormat,
    language: DateContext.get('language'),
  },
  renderInput: function (context) {
    return this.fieldTemplate(context);
  },
  click: function (event) {
    var clickedElement = $(event.currentTarget).parent();
    var picker = this.$('.datetimepicker');

    Datepicker.init(picker, this.datetimepickerOptions);
    clickedElement.datetimepicker('show');

    picker.on(
      'changeDate',
      function (e) {
        this.setCurrentValue(this.$(e.target).find('input[type="text"]').val());
      }.bind(this)
    );
  },
  updateModel: function () {
    var data = this.$('.field-input:first input[type="text"]').val();
    data = '' === data ? this.attribute.empty_value : data;

    this.setCurrentValue(data);
  },
});
