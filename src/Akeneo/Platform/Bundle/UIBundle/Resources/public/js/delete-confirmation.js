import _ from 'underscore';
import __ from 'oro/translator';
import Backbone from 'backbone';

export default Backbone.BootstrapModal.extend({
  /**
   * @param {Object} options
   */
  initialize: function (options) {
    options = _.extend(
      {
        title: __('pim_common.confirm_deletion'),
        okText: __('pim_common.ok'),
        buttonClass: 'AknButton--important',
        illustrationClass: 'delete',
        cancelText: __('pim_common.cancel'),
      },
      options
    );

    arguments[0] = options;

    Backbone.BootstrapModal.prototype.initialize.apply(this, arguments);
  },
});
