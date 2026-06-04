import router from 'pim/router';
import __ from 'oro/translator';
let routeParams = {};
let render = (name, params) => {
  document.title = __('pim_title.' + name, params);
};

router.on('route_complete', name => {
  render(name, routeParams);
});

export default {
  set: params => {
    routeParams = params;
  },

  render: render,
};
