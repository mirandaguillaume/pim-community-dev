import _ from 'underscore';
import Uuid from 'pim/form/common/meta/uuid';
import template from 'pim/template/product/meta/uuid';

export default Uuid.extend({
  className: 'AknColumn-block',
  template: _.template(template),
});
