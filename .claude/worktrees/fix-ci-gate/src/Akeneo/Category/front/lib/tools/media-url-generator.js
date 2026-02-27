"use strict";
var __assign = (this && this.__assign) || function () {
    __assign = Object.assign || function(t) {
        for (var s, i = 1, n = arguments.length; i < n; i++) {
            s = arguments[i];
            for (var p in s) if (Object.prototype.hasOwnProperty.call(s, p))
                t[p] = s[p];
        }
        return t;
    };
    return __assign.apply(this, arguments);
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.getMediaPreviewUrl = exports.getImageDownloadUrl = exports.copyToClipboard = exports.canCopyToClipboard = void 0;
var File_1 = require("../models/File");
var canCopyToClipboard = function () { return 'clipboard' in navigator; };
exports.canCopyToClipboard = canCopyToClipboard;
var copyToClipboard = function (text) { return canCopyToClipboard() && navigator.clipboard.writeText(text); };
exports.copyToClipboard = copyToClipboard;
var getImageDownloadUrl = function (router, image) {
    var path = !(0, File_1.isFileEmpty)(image) ? image.filePath : 'undefined';
    var filename = encodeURIComponent(path);
    return router.generate('pim_enrich_media_download', { filename: filename });
};
exports.getImageDownloadUrl = getImageDownloadUrl;
var getMediaPreviewUrl = function (router, mediaPreview) {
    var isUrlEncoded = false;
    try {
        isUrlEncoded = mediaPreview.data !== decodeURIComponent(mediaPreview.data);
    }
    catch (error) {
        if (!(error instanceof URIError)) {
            throw error;
        }
    }
    return router.generate('pim_enriched_category_rest_image_preview', __assign(__assign({}, mediaPreview), { data: btoa(isUrlEncoded ? mediaPreview.data : encodeURI(mediaPreview.data)) }));
};
exports.getMediaPreviewUrl = getMediaPreviewUrl;
//# sourceMappingURL=media-url-generator.js.map