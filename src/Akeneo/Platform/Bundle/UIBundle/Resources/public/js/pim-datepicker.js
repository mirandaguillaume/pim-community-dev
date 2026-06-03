'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var $ = __pimInterop(require('jquery'));
var _ = __pimInterop(require('underscore'));
var __ = __pimInterop(require('oro/translator'));
require('bootstrap.datetimepicker');

module.exports = {
  options: {
    language: 'en',
    pickTime: false,
  },
  init: function ($target, options) {
    options = $.extend(true, {}, this.options, options);

    if ('en' !== options.language && undefined === $.fn.datetimepicker.dates[options.language]) {
      var languageOptions = {};
      var defaultOptions = $.fn.datetimepicker.dates.en;

      _.each(_.keys(defaultOptions), function (key) {
        languageOptions[key] = [];
        _.each(defaultOptions[key], function (value) {
          languageOptions[key].push(__('datetimepicker.' + key + '.' + value));
        });
      });

      $.fn.datetimepicker.dates[options.language] = languageOptions;
    }

    $target.datetimepicker(options);

    return $target;
  },
};
