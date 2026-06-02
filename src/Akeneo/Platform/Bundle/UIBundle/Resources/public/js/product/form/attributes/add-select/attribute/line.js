'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

require('jquery');
var _ = __pimInterop(require('underscore'));
var BaseLine = __pimInterop(require('pim/common/add-select/line'));
var template = __pimInterop(require('pim/template/product/add-select/attribute/line'));

module.exports = BaseLine.extend({
  template: _.template(template),
});
