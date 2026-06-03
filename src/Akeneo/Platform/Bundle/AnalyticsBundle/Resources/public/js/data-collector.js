'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var $ = __pimInterop(require('jquery'));
require('underscore');
var Routing = __pimInterop(require('routing'));

module.exports = {
  collect: function (route) {
    return $.getJSON(Routing.generate(route));
  },
};
