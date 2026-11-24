/* globals-prelude.js
 * expose-loader uses __webpack_require__ runtime internals that RSPack 1.x does not
 * implement (RSPack uses __rspack_require__). As a result, expose-loader silently
 * fails to assign window.$, window.jQuery, and window.Backbone. This prelude loads
 * these modules through a plain require() call so they are guaranteed to be on window
 * before the AMD bootstrap runs.
 */
var jq = require('jquery');
window.$ = jq;
window.jQuery = jq;

window.Backbone = require('backbone');
window._ = require('underscore');

// Summernote (lib/summernote) reads its HTML sanitizer from window, as it reads jQuery: RSPack does
// not enable AMD, so the vendored UMD wrapper always takes its browser-globals branch.
window.DOMPurify = require('dompurify');

// Expose the require polyfill to window for Twig inline <script> tags
// that call require() to load AMD modules at runtime.
window.require = require('require-polyfill');
