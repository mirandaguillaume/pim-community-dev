'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var __ = __pimInterop(require('oro/translator'));
var BaseController = __pimInterop(require('pim/controller/base'));
var Error = __pimInterop(require('pim/error'));

module.exports = BaseController.extend({
  formPromise: null,

  /**
   * {@inheritdoc}
   */
  renderRoute: function (route, path) {
    this.formPromise = this.renderForm(route, path).fail(response => {
      const message =
        response && response.responseJSON
          ? response.responseJSON.message
          : __('pim_enrich.entity.fallback.generic_error');
      const status = response && response.status ? response.status : 500;

      const errorView = new Error(message, status);
      errorView.setElement(this.$el).render();
    });

    return this.formPromise;
  },

  /**
   * Render the from for given route
   *
   * @param {String} route
   * @param {String} path
   *
   * @return {Promise}
   */
  renderForm: function () {
    throw new Error('Method renderForm is abstract and must be implemented!');
  },

  /**
   * {@inheritdoc}
   */
  remove: function () {
    if (null === this.formPromise) {
      return;
    }

    this.formPromise.then(form => {
      if (form && typeof form.shutdown === 'function') {
        form.shutdown();
      }
    });

    BaseController.prototype.remove.apply(this, arguments);
  },
});
