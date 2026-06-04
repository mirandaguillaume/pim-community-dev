import AbstractListener from 'oro/datagrid/abstract-listener';

export default AbstractListener.extend({
  /** @param {Call} */
  processCallback: null,

  /**
   * Initialize listener object
   *
   * @param {Object} options
   */
  initialize: function (options) {
    if (!_.has(options, 'processCallback')) {
      throw new Error('Process callback is not specified');
    }

    this.processCallback = options.processCallback;

    AbstractListener.prototype.initialize.apply(this, arguments);
  },

  /**
   * Execute callback
   *
   * @param {*} value Value of model property with name of this.dataField
   * @param {Backbone.Model} model
   * @protected
   */
  _processValue: function (value, model) {
    this.processCallback(value, model, this);
  },
});
