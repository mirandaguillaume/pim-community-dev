'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var router = __pimInterop(require('pim/router'));
var __ = __pimInterop(require('oro/translator'));
let routeParams = {};
let render = (name, params) => {
  document.title = __('pim_title.' + name, params);
};

router.on('route_complete', name => {
  render(name, routeParams);
});

module.exports = {
  set: params => {
    routeParams = params;
  },

  render: render,
};
