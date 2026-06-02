'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var MediaField = __pimInterop(require('pim/media-field'));

module.exports = MediaField.extend({
  uploadRouteName: 'akeneo_file_storage_upload_image',
});
