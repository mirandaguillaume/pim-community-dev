import 'jquery';
import ConfigProvider from 'pim/form-config-provider';
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

export default {
  getFormExtensions,
  getFormMeta,
};
