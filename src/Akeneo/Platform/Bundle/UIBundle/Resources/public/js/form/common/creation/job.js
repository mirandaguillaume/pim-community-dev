function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var $ = __pimInterop(require('jquery'));
var _ = __pimInterop(require('underscore'));
require('backbone');
require('routing');
var BaseForm = __pimInterop(require('pim/form'));
require('pim/user-context');
require('pim/i18n');
var __ = __pimInterop(require('oro/translator'));
var template = __pimInterop(require('pim/template/form/creation/job'));
var BaseFetcher = __pimInterop(require('pim/base-fetcher'));

module.exports = BaseForm.extend({
  options: {},
  template: _.template(template),
  events: {
    'change select': 'updateModel',
  },

  /**
   * Configure the form
   *
   * @return {Promise}
   */
  configure() {
    const jobType = this.options.config.type;
    const fetcher = new BaseFetcher({
      urls: {list: this.options.config.url},
    });

    return fetcher.search({jobType}).then(jobs => {
      this.jobs = jobs;
      BaseForm.prototype.configure.apply(this, arguments);
    });
  },

  /**
   * Model update callback
   */
  updateModel(event) {
    const option = this.$(event.target);
    const optionParent = $(':selected', option).closest('optgroup');

    this.getFormModel().set({
      alias: option.val(),
      connector: optionParent.attr('label'),
    });
  },

  /**
   * Renders the form
   *
   * @return {Promise}
   */
  render() {
    if (!this.configured) return this;

    const errors = this.getRoot().validationErrors || [];
    const identifier = this.options.config.identifier || 'alias';

    this.$el.html(
      this.template({
        label: __(this.options.config.label),
        jobs: this.jobs,
        required: __('pim_common.required_label'),
        selectedJobType: this.getFormData().alias,
        errors: errors.filter(error => error.path === identifier),
        __,
      })
    );

    this.$el.find('.job-input').select2();

    this.delegateEvents();
  },
});
