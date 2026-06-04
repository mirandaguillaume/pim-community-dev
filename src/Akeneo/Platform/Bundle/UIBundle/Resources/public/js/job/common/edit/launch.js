import $ from 'jquery';
import _ from 'underscore';
import __ from 'oro/translator';
import BaseForm from 'pim/form';
import Routing from 'routing';
import router from 'pim/router';
import propertyAccessor from 'pim/common/property';
import * as messenger from 'oro/messenger';
import LoadingMask from 'oro/loading-mask';
import template from 'pim/template/export/common/edit/launch';
import analytics from 'pim/analytics';

export default BaseForm.extend({
  template: _.template(template),
  events: {
    'click .AknButton': 'launch',
  },

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
    this.isVisible().then(
      function (isVisible) {
        this.$el.empty();
        if (!isVisible) {
          return this;
        }

        this.$el.html(
          this.template({
            label: __(this.config.label),
            buttonClass: this.config.buttonClass ?? '',
            title: this.config.title ? __(this.config.title) : '',
          })
        );
      }.bind(this)
    );

    this.delegateEvents();

    return this;
  },

  /**
   * Launch the job
   */
  launch: function () {
    var loadingMask = new LoadingMask();
    loadingMask.render().$el.appendTo(this.getRoot().$el).show();
    $.post(this.getUrl())
      .then(function (response) {
        if (response.redirectUrl) {
          router.redirect(response.redirectUrl);
        } else {
          router.reloadPage();
        }

        analytics.appcuesTrack('job-instance:export:launched', {
          url: this.url,
        });
      })
      .fail(function () {
        messenger.notify('error', __('pim_import_export.form.job_instance.fail.launch'));
      })
      .always(function () {
        loadingMask.hide().$el.remove();
      });
  },

  /**
   * Get the route to launch the job
   *
   * @return {string}
   */
  getUrl: function () {
    var params = {};
    params[this.config.identifier.name] = propertyAccessor.accessProperty(
      this.getFormData(),
      this.config.identifier.path
    );

    return Routing.generate(this.config.route, params);
  },

  /**
   * Should this extension render
   *
   * @return {Promise}
   */
  isVisible: function () {
    return $.Deferred().resolve(true).promise();
  },
});
