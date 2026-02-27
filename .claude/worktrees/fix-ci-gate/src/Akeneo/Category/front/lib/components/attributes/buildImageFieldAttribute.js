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
var __read = (this && this.__read) || function (o, n) {
    var m = typeof Symbol === "function" && o[Symbol.iterator];
    if (!m) return o;
    var i = m.call(o), r, ar = [], e;
    try {
        while ((n === void 0 || n-- > 0) && !(r = i.next()).done) ar.push(r.value);
    }
    catch (error) { e = { error: error }; }
    finally {
        try {
            if (r && !r.done && (m = i["return"])) m.call(i);
        }
        finally { if (e) throw e.error; }
    }
    return ar;
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.buildImageFieldAttribute = void 0;
var jsx_runtime_1 = require("react/jsx-runtime");
var akeneo_design_system_1 = require("akeneo-design-system");
var types_1 = require("./types");
var templateAttributesFactory_1 = require("./templateAttributesFactory");
var fp_1 = require("lodash/fp");
var shared_1 = require("@akeneo-pim-community/shared");
var usePreventClosing_1 = require("../../hooks/usePreventClosing");
var fullscreen_preview_1 = require("../file/preview/fullscreen-preview");
var media_url_generator_1 = require("../../tools/media-url-generator");
var MediaPreview_1 = require("../../models/MediaPreview");
var unMemoizedBuildImageFieldAttribute = function (attribute) {
    var Component = function (_a) {
        var channel = _a.channel, locale = _a.locale, value = _a.value, onChange = _a.onChange;
        var translate = (0, shared_1.useTranslate)();
        var _b = __read((0, shared_1.useUploader)('pim_enriched_category_rest_file_upload'), 2), uploader = _b[0], isUploading = _b[1];
        (0, usePreventClosing_1.usePreventClosing)(function () { return isUploading; }, translate('pim_enrich.confirmation.discard_changes', { entity: 'category' }));
        var router = (0, shared_1.useRouter)();
        var _c = __read((0, akeneo_design_system_1.useBooleanState)(), 3), isFullscreenModalOpen = _c[0], openFullscreenModal = _c[1], closeFullscreenModal = _c[2];
        var inModal = (0, akeneo_design_system_1.useInModal)();
        var imageInfo = !(0, types_1.isImageAttributeInputValue)(value) ? null : value;
        var downloadFilename = imageInfo === null || imageInfo === void 0 ? void 0 : imageInfo.originalFilename;
        var downloadUrl = imageInfo ? (0, media_url_generator_1.getImageDownloadUrl)(router, imageInfo) : '';
        var thumbnailUrl = (0, media_url_generator_1.getMediaPreviewUrl)(router, {
            type: MediaPreview_1.MediaPreviewType.Thumbnail,
            attributeCode: attribute.code,
            data: imageInfo ? imageInfo.filePath : '',
        });
        return ((0, jsx_runtime_1.jsxs)(akeneo_design_system_1.Field, __assign({ channel: channel.label, label: (0, templateAttributesFactory_1.getLabelFromAttribute)(attribute, locale), locale: locale }, { children: [(0, jsx_runtime_1.jsxs)(akeneo_design_system_1.MediaFileInput, __assign({ value: imageInfo, onChange: onChange, placeholder: translate('pim_common.media_upload'), uploadingLabel: translate('pim_common.media_uploading'), uploadErrorLabel: translate('pim_common.media_upload_error'), clearTitle: translate('pim_common.clear_value'), thumbnailUrl: thumbnailUrl, uploader: uploader }, { children: [downloadUrl && downloadFilename && ((0, jsx_runtime_1.jsx)(akeneo_design_system_1.IconButton, { href: downloadUrl, target: "_blank", download: downloadFilename, icon: (0, jsx_runtime_1.jsx)(akeneo_design_system_1.DownloadIcon, {}, void 0), title: "Download" }, void 0)), !inModal && (0, jsx_runtime_1.jsx)(akeneo_design_system_1.IconButton, { onClick: openFullscreenModal, icon: (0, jsx_runtime_1.jsx)(akeneo_design_system_1.FullscreenIcon, {}, void 0), title: "Fullscreen" }, void 0)] }), void 0), isFullscreenModalOpen && !inModal && imageInfo && ((0, jsx_runtime_1.jsx)(fullscreen_preview_1.FullscreenPreview, { onClose: closeFullscreenModal, attribute: attribute, data: imageInfo, label: attribute.labels[locale] }, void 0))] }), void 0));
    };
    Component.displayName = 'ImageFieldAttribute';
    return Component;
};
exports.buildImageFieldAttribute = (0, fp_1.memoize)(unMemoizedBuildImageFieldAttribute);
//# sourceMappingURL=buildImageFieldAttribute.js.map