import _ from 'underscore';
import __ from 'oro/translator';
import BaseForm from 'pim/form';
import template from 'pim/template/import/file-path';
import {Badge} from 'akeneo-design-system';

export default BaseForm.extend({
  className: 'AknCenteredBox',
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
    const {label, path, badge} = this.getStorageInfo();

    this.$el.html(this.template({label, path}));
    this.renderBadge(badge);

    this.delegateEvents();

    return BaseForm.prototype.render.apply(this, arguments);
  },

  renderBadge: function (badge) {
    if (null === badge) return;

    this.renderReact(Badge, {children: badge, level: 'secondary'}, this.$el.find('.storage_type')[0]);
  },

  getStorageInfo: function () {
    const {configuration} = this.getFormData();
    const storageType = configuration.storage?.type ?? 'none';
    const filePath = configuration.storage?.file_path ?? '';

    switch (storageType) {
      case 'sftp':
      case 'amazon_s3':
      case 'microsoft_azure':
      case 'google_cloud_storage':
        return {
          badge: __(`pim_import_export.form.job_instance.storage_form.connection.${storageType}`),
          label: __(this.config.label),
          path: filePath,
        };
      case 'local':
      case 'none':
      default:
        return {
          badge: null,
          label: __(this.config.label),
          path: filePath,
        };
    }
  },
});
