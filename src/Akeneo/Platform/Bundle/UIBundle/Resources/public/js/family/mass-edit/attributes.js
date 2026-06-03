'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var BaseAttributesView = __pimInterop(require('pim/family-edit-form/attributes/attributes'));
var mediator = __pimInterop(require('oro/mediator'));

module.exports = BaseAttributesView.extend({
  lock: false,

  /**
   * {@inheritdoc}
   */
  configure: function () {
    mediator.on('mass-edit:form:lock', this.onLock.bind(this));

    mediator.on('mass-edit:form:unlock', this.onUnlock.bind(this));

    return BaseAttributesView.prototype.configure.apply(this, arguments);
  },

  /**
   * {@inheritdoc}
   */
  toggleAttribute: function () {
    if (this.lock) {
      return false;
    }

    BaseAttributesView.prototype.toggleAttribute.apply(this, arguments);
  },

  /**
   * {@inheritdoc}
   */
  onRemoveAttribute: function () {
    if (this.lock) {
      return false;
    }

    BaseAttributesView.prototype.onRemoveAttribute.apply(this, arguments);
  },

  /**
   * Lock event callback
   */
  onLock: function () {
    this.lock = true;
  },

  /**
   * Unlock event callback
   */
  onUnlock: function () {
    this.lock = false;
  },
});
