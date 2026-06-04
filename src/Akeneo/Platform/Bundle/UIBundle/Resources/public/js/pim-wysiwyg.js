import $ from 'jquery';
import _ from 'underscore';
import Backbone from 'backbone';
import 'summernote';
/**
 * Wysiwyg editor default configuration
 */
var config = {
  disableResizeEditor: true,
  height: 200,
  iconPrefix: 'icon-',
  toolbar: [
    ['font', ['bold', 'italic', 'underline', 'clear']],
    ['para', ['ul', 'ol']],
    ['insert', ['link']],
    ['view', ['codeview']],
  ],
  prettifyHtml: false,
  direction: 'auto',
};

Backbone.Router.prototype.on('route', function () {
  $('textarea.wysiwyg').each(function () {
    $(this).destroy();
  });
});

export default {
  /**
   * Wysiwyg editor settings
   */
  settings: [],

  /**
   * Initialise the wysiwyg
   *
   * @param {jquery} $el
   * @param {Array}  options
   *
   * @returns {Object}
   */
  init: function ($el, options) {
    this.settings = _.extend(_.clone(config), options);

    $el.summernote(this.settings);

    return this;
  },

  /**
   * Put the wysiwyg in readonly mode for the given element
   *
   * @param {jquery} $el
   *
   * @returns {Object}
   */
  readonly: function ($el) {
    var editable = $el.parent().find('.note-editable');

    editable.attr('contenteditable', false);

    return this;
  },
};
