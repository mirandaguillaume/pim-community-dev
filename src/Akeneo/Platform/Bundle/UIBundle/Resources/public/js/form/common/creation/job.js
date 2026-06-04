import $ from 'jquery';
import _ from 'underscore';
import 'backbone';
import 'routing';
import BaseForm from 'pim/form';
import 'pim/user-context';
import 'pim/i18n';
import __ from 'oro/translator';
import template from 'pim/template/form/creation/job';
import BaseFetcher from 'pim/base-fetcher';

export default BaseForm.extend({
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
