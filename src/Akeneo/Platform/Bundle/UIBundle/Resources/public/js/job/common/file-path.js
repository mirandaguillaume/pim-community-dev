'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var _ = __pimInterop(require('underscore'));
var __ = __pimInterop(require('oro/translator'));
var BaseForm = __pimInterop(require('pim/form'));
var template = __pimInterop(require('pim/template/import/file-path'));
var {Badge} = __pimInterop(require('akeneo-design-system'));

module.exports = BaseForm.extend({
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
