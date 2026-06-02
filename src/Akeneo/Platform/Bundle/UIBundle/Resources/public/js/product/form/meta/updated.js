'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var _ = __pimInterop(require('underscore'));
var Updated = __pimInterop(require('pim/form/common/meta/updated'));
var template = __pimInterop(require('pim/template/product/meta/updated'));

module.exports = Updated.extend({
  className: 'AknColumn-block',

  template: _.template(template),
});
