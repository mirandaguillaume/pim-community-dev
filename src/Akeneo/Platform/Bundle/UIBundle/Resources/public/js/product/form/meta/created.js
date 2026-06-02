'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var _ = __pimInterop(require('underscore'));
var Created = __pimInterop(require('pim/form/common/meta/created'));
var template = __pimInterop(require('pim/template/product/meta/created'));

module.exports = Created.extend({
  className: 'AknColumn-block',

  template: _.template(template),
});
