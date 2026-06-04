import _ from 'underscore';
import 'oro/translator';
import BaseForm from 'pim/form';
import template from 'pim/template/family/tab/attributes/toolbar';

export default BaseForm.extend({
  className: 'AknGridToolbar',
  template: _.template(template),
  readOnly: false,

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
  configure: function () {
    this.listenTo(
      this.getRoot(),
      'pim_enrich:form:update_read_only',
      function (readOnly) {
        this.readOnly = readOnly;

        this.render();
      }.bind(this)
    );

    BaseForm.prototype.configure.apply(this, arguments);
  },

  /**
   * {@inheritdoc}
   */
  render: function () {
    if (!this.configured) {
      return this;
    }

    if (this.readOnly) {
      this.$el.empty();

      return this;
    }

    this.$el.html(this.template({}));

    this.renderExtensions();
  },
});
