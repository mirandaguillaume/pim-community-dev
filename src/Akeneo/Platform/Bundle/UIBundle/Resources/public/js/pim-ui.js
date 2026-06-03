'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var $ = __pimInterop(require('jquery'));
var initSelect2 = __pimInterop(require('pim/initselect2'));
var wysiwyg = __pimInterop(require('wysiwyg'));
require('bootstrap');

module.exports = function ($target) {
  // Apply Select2
  initSelect2.init($target);

  // Initialize tooltip
  $target.find('[data-toggle="tooltip"]').tooltip();

  // Initialize popover
  $target.find('[data-toggle="popover"]').popover();

  // Activate a form tab
  $target.find('li.tab.active a').each(function () {
    var paneId = $(this).attr('href');
    $(paneId).addClass('active');
  });

  $target.find('textarea.wysiwyg[id]:not([aria-hidden])').each(function () {
    if (!$(this).closest('.attribute-field').hasClass('scopable')) {
      wysiwyg.init($(this));
    }
  });
};
