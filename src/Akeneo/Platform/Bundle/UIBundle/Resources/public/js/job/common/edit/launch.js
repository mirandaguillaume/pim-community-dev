'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var $ = __pimInterop(require('jquery'));
var _ = __pimInterop(require('underscore'));
var __ = __pimInterop(require('oro/translator'));
var BaseForm = __pimInterop(require('pim/form'));
var Routing = __pimInterop(require('routing'));
var router = __pimInterop(require('pim/router'));
var propertyAccessor = __pimInterop(require('pim/common/property'));
var messenger = __pimInterop(require('oro/messenger'));
var LoadingMask = __pimInterop(require('oro/loading-mask'));
var template = __pimInterop(require('pim/template/export/common/edit/launch'));
var analytics = __pimInterop(require('pim/analytics'));

module.exports = BaseForm.extend({
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
