import _ from 'underscore';
import __ from 'oro/translator';
import BaseForm from 'pim/form';
import template from 'pim/template/form/meta';

export default BaseForm.extend({
  template: _.template(template),

  config: {},

  /**
   * {@inheritdoc}
   */
  initialize: function (meta) {
    this.config = meta.config;

    return BaseForm.prototype.initialize.apply(this, arguments);
  },

  /**
   * {@inheritdoc}
   */
  render: function () {
    this.$el.empty();

    if (!_.isEmpty(this.extensions)) {
      this.$el.html(
        this.template({
          label: __(this.config.label),
        })
      );
    }

    return BaseForm.prototype.render.apply(this, arguments);
  },
});
