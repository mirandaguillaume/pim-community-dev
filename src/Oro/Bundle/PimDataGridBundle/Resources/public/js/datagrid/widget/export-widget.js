import $ from 'jquery';
import __ from 'oro/translator';
import Backbone from 'backbone';
import messenger from 'oro/messenger';
import Error from 'oro/error';
import Routing from 'routing';
import React from 'react';
import {Link} from 'akeneo-design-system';

export default Backbone.View.extend({
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
