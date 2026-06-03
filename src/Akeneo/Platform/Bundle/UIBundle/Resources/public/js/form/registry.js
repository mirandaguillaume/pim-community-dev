'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

require('jquery');
var ConfigProvider = __pimInterop(require('pim/form-config-provider'));
const getFormExtensions = formMeta => {
  return ConfigProvider.getExtensionMap().then(extensionMap => {
    return extensionMap.filter(extension => extension.parent === formMeta.code);
  });
};

const getFormMeta = formName => {
  return ConfigProvider.getExtensionMap().then(extensionMap => {
    return extensionMap.find(extension => extension.code === formName);
  });
};

module.exports = {
  getFormExtensions,
  getFormMeta,
};
