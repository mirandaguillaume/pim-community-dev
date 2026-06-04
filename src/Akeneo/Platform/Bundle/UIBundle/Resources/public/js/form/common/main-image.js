import _ from 'underscore';
import BaseForm from 'pim/form';
import template from 'pim/template/form/main-image';
import MediaUrlGenerator from 'pim/media-url-generator';
import $ from 'jquery';

export default BaseForm.extend({
  tagName: 'img',
  className: 'AknTitleContainer-image',
  template: _.template(template),

  /**
   * {@inheritdoc}
   */
  initialize: function (config) {
    this.config = config.config;
    this.imagePath = null;

    BaseForm.prototype.initialize.apply(this, arguments);
  },

  /**
   * {@inheritdoc}
   */
  configure() {
    this.listenTo(this.getRoot(), 'pim_enrich:form:entity:post_fetch', this.render.bind(this));
  },

  /**
   * {@inheritdoc}
   */
  render: function () {
    if (null === this.getPath()) {
      return;
    }

    this.el.src = this.getPath();

    this.$el.one('error', function () {
      $(this).attr('src', MediaUrlGenerator.getMediaShowUrl(null, 'thumbnail_small'));
    });

    return BaseForm.prototype.render.apply(this, arguments);
  },

  /**
   * Returns the path of the image to display
   *
   * @returns {string}
   */
  getPath: function () {
    if (undefined !== this.config.path) {
      return this.config.path;
    }

    const filePath = this.imagePath ?? _.result(this.getFormData().meta.image, 'filePath', null);

    if (filePath === null && undefined !== this.config.fallbackPath) {
      return this.config.fallbackPath;
    }

    return MediaUrlGenerator.getMediaShowUrl(filePath, 'thumbnail_small');
  },
});
