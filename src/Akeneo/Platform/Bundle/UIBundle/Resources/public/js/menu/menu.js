import _ from 'underscore';
import BaseForm from 'pim/form';
import template from 'pim/template/menu/menu';

export default BaseForm.extend({
  className: 'AknHeader',
  template: _.template(template),

  /**
   * {@inheritdoc}
   */
  render: function () {
    this.$el.empty().append(this.template());

    return BaseForm.prototype.render.apply(this, arguments);
  },

  /**
   * {@inheritdoc}
   */
  renderExtension: function (extension) {
    if (
      !_.isEmpty(extension.options.config) &&
      (!extension.options.config.to || extension.options.config.isLandingSectionPage) &&
      _.isFunction(extension.hasChildren) &&
      !extension.hasChildren()
    ) {
      return;
    }

    BaseForm.prototype.renderExtension.apply(this, arguments);
  },
});
