function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var $ = __pimInterop(require('jquery'));
var formBuilder = __pimInterop(require('pim/form-builder'));
formBuilder.build('pim-app').then(function (form) {
  form.setElement($('.app'));
  form.render();
});
