function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

require('jquery');
var _ = __pimInterop(require('underscore'));
var requireContext = __pimInterop(require('require-context'));

module.exports = function (modules, cb) {
  var resolvedModules = [];

  if (typeof modules === 'string') {
    return requireContext(modules);
  } else {
    _.each(modules, function (module) {
      var resolvedModule = requireContext(module);
      resolvedModules.push(resolvedModule);
    });
  }

  if (cb) {
    cb.apply(this, resolvedModules);
  }
};
