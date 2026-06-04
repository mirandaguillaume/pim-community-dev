import _ from 'underscore';
import StringCell from 'oro/datagrid/string-cell';
import MediaUrlGenerator from 'pim/media-url-generator';
import template from 'pim/template/datagrid/cell/image-cell';
import $ from 'jquery';

export default StringCell.extend({
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
