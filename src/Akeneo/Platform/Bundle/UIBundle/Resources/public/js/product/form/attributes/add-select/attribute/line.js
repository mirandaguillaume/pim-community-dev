import 'jquery';
import _ from 'underscore';
import BaseLine from 'pim/common/add-select/line';
import template from 'pim/template/product/add-select/attribute/line';

export default BaseLine.extend({
  template: _.template(template),
});
