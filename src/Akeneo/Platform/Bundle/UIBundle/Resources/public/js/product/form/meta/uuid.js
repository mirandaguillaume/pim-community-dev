'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var _ = __pimInterop(require('underscore'));
var Uuid = __pimInterop(require('pim/form/common/meta/uuid'));
var template = __pimInterop(require('pim/template/product/meta/uuid'));

module.exports = Uuid.extend({
  className: 'AknColumn-block',
  template: _.template(template),
});
