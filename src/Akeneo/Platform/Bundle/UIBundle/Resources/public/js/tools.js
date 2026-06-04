import _ from 'underscore';
import requireContext from 'require-context';

export default {
  /**
   * Loads dynamic list of modules and execute callback function with passed modules
   *
   * @param {Object.<string, string>} modules where keys are formal module names and values are actual
   * @param {function (Object)} callback
   */
  loadModules: function (modules, callback) {
    var arrayArguments = _.object(requirements, arguments);
    var requirements = _.values(modules);

    // `require.ensure([], cb)` with an empty deps array is a no-op async
    // boundary that webpack compiled to `Promise.resolve().then(cb)`. The
    // CJS-parser feature is not recognised inside an ES module (bare `require`
    // would be an undefined runtime reference), so use the Promise directly.
    Promise.resolve().then(function () {
      _.each(
        modules,
        _.bind(function (value, key) {
          var module = requireContext(value);
          modules[key] = module;
        }, arrayArguments)
      );
      callback(modules);
    });
  },
};
