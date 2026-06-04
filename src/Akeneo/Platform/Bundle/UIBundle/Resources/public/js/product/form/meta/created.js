import _ from 'underscore';
import Created from 'pim/form/common/meta/created';
import template from 'pim/template/product/meta/created';

export default Created.extend({
  className: 'AknColumn-block',

  template: _.template(template),
});
