'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var BaseForm = __pimInterop(require('pim/form'));
var __ = __pimInterop(require('oro/translator'));
var mediator = __pimInterop(require('oro/mediator'));

module.exports = BaseForm.extend({
  count: null,

  /**
   * {@inheritdoc}
   */
  initialize(config) {
    this.config = config.config;

    mediator.once('grid_load:start', this.setupCount.bind(this));
    mediator.on('grid_load:complete', this.setupCount.bind(this));
  },

  /**
   * {@inheritdoc}
   */
  render() {
    if (null !== this.count) {
      this.$el.text(__(this.config.title, {count: this.count}, this.count));
    } else if (false === this.config.countable) {
      this.$el.text(__(this.config.title));
    }
  },

  /**
   * Setup the count from the collection
   *
   * @param {Object} collection
   */
  setupCount(collection) {
    this.count = collection.state.totalRecords;

    this.render();
  },
});
