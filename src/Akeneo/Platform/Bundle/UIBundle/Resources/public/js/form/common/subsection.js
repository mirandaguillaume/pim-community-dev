import _ from 'underscore';
import __ from 'oro/translator';
import BaseForm from 'pim/form';
import template from 'pim/template/form/subsection';

export default BaseForm.extend({
  className: 'AknSubsection',
  template: _.template(template),

  /**
   * {@inheritdoc}
   */
  initialize: function (config) {
    this.config = config.config;

    BaseForm.prototype.initialize.apply(this, arguments);
  },

  /**
   * {@inheritdoc}
   */
  render: function () {
    this.$el.empty().append(
      this.template({
        title: __(this.config.title),
      })
    );

    return BaseForm.prototype.render.apply(this, arguments);
  },
});
