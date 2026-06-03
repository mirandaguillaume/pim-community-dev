function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var $ = __pimInterop(require('jquery'));
var __ = __pimInterop(require('oro/translator'));
var Backbone = __pimInterop(require('backbone'));
var messenger = __pimInterop(require('oro/messenger'));
var Error = __pimInterop(require('oro/error'));
var Routing = __pimInterop(require('routing'));
var React = __pimInterop(require('react'));
var {Link} = require('akeneo-design-system');

module.exports = Backbone.View.extend({
  action: null,

  initialize: function (action) {
    this.action = action;
  },

  run: function () {
    $.post(this.action.getLink(), this.action.getActionParameters())
      .done(function (data) {
        const title = __('pim_datagrid.mass_action.quick_export.success');
        const link = React.createElement(
          Link,
          {key: data.job_id, href: `#${Routing.generate('akeneo_job_process_tracker_details', {id: data.job_id})}`},
          __('pim_datagrid.mass_action.quick_export.flash.message')
        );

        messenger.notify('success', title, link);
      })
      .fail(function (jqXHR) {
        if (jqXHR.status === 401) {
          Error.dispatch(null, jqXHR);
        } else {
          messenger.notify('error', __(jqXHR.responseText));
        }
      });
  },
});
