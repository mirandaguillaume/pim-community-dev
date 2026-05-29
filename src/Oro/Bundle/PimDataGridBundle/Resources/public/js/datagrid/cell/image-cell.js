function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var _ = __pimInterop(require('underscore'));
var StringCell = __pimInterop(require('oro/datagrid/string-cell'));
var MediaUrlGenerator = __pimInterop(require('pim/media-url-generator'));
var template = __pimInterop(require('pim/template/datagrid/cell/image-cell'));
var $ = __pimInterop(require('jquery'));
('use strict');

module.exports = StringCell.extend({
  template: _.template(template),

  /**
   * Render an image.
   */
  render: function () {
    const image = this.formatter.fromRaw(this.model.get(this.column.get('name')));

    const src = MediaUrlGenerator.getMediaShowUrl(image.filePath, 'thumbnail_small');
    this.$el.empty().html(this.getTemplate({label: image.originalFilename, src}));

    this.$el.find('img').one('error', function () {
      $(this).attr('src', MediaUrlGenerator.getMediaShowUrl(null, 'thumbnail_small'));
    });

    return this;
  },

  /**
   * Returns the template used to show the image.
   *
   * This function can be overridden to alter the way the image is shown.
   *
   * @returns {string}
   */
  getTemplate(params) {
    return this.template(params);
  },
});
