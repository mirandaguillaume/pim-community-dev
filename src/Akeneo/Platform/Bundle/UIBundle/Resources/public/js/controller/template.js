'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var $ = __pimInterop(require('jquery'));
require('underscore');
var BaseController = __pimInterop(require('pim/controller/base'));

module.exports = BaseController.extend({
  /**
   * {@inheritdoc}
   */
  renderRoute: function (route, path) {
    return $.get(path).then(this.renderTemplate.bind(this)).promise();
  },

  /**
   * Add the given content to the current container
   *
   * @param {String} content
   */
  renderTemplate: function (content) {
    if (!this.active) {
      return;
    }

    this.$el.html(content);
  },
});
