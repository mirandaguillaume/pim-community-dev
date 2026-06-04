function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var moduleRegistry = __pimInterop(require('module-registry'));

module.exports = function (moduleName) {
  return moduleRegistry(moduleName);
};
