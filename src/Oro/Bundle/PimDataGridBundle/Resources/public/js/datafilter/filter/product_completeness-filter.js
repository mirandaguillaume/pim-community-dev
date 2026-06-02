function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

require('underscore');
var SelectFilter = __pimInterop(require('oro/datafilter/select-filter'));
('use strict');

module.exports = SelectFilter.extend({});
