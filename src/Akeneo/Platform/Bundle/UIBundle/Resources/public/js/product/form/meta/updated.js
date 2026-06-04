import _ from 'underscore';
import Updated from 'pim/form/common/meta/updated';
import template from 'pim/template/product/meta/updated';

export default Updated.extend({
  className: 'AknColumn-block',

  template: _.template(template),
});
