"use strict";
var __makeTemplateObject = (this && this.__makeTemplateObject) || function (cooked, raw) {
    if (Object.defineProperty) { Object.defineProperty(cooked, "raw", { value: raw }); } else { cooked.raw = raw; }
    return cooked;
};
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
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.FullscreenPreview = void 0;
var jsx_runtime_1 = require("react/jsx-runtime");
var styled_components_1 = __importDefault(require("styled-components"));
var akeneo_design_system_1 = require("akeneo-design-system");
var shared_1 = require("@akeneo-pim-community/shared");
var media_preview_1 = require("./media-preview");
var media_url_generator_1 = require("../../../tools/media-url-generator");
var MediaPreview_1 = require("../../../models/MediaPreview");
var Border = styled_components_1.default.div(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  display: flex;\n  flex-direction: column;\n  align-items: center;\n  padding: 20px;\n  border: 1px solid ", ";\n  max-height: 100%;\n  gap: 20px;\n"], ["\n  display: flex;\n  flex-direction: column;\n  align-items: center;\n  padding: 20px;\n  border: 1px solid ", ";\n  max-height: 100%;\n  gap: 20px;\n"])), (0, akeneo_design_system_1.getColor)('grey', 80));
var BrandedTitle = (0, styled_components_1.default)(akeneo_design_system_1.Modal.Title)(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n  color: ", ";\n"], ["\n  color: ", ";\n"])), (0, akeneo_design_system_1.getColor)('brand', 100));
var ButtonContainer = styled_components_1.default.div(templateObject_3 || (templateObject_3 = __makeTemplateObject(["\n  display: flex;\n  gap: 10px;\n  align-items: center;\n"], ["\n  display: flex;\n  gap: 10px;\n  align-items: center;\n"])));
var buttonProps = {
    level: 'tertiary',
    ghost: true,
};
var FullscreenPreview = function (_a) {
    var label = _a.label, data = _a.data, attribute = _a.attribute, onClose = _a.onClose;
    var translate = (0, shared_1.useTranslate)();
    var router = (0, shared_1.useRouter)();
    var downloadUrl = data ? (0, media_url_generator_1.getImageDownloadUrl)(router, data) : '';
    var previewUrl = (0, media_url_generator_1.getMediaPreviewUrl)(router, {
        type: MediaPreview_1.MediaPreviewType.Preview,
        attributeCode: attribute.code,
        data: data && data.filePath ? data.filePath : '',
    });
    return ((0, jsx_runtime_1.jsxs)(akeneo_design_system_1.Modal, __assign({ onClose: onClose, closeTitle: translate('pim_common.close') }, { children: [(0, jsx_runtime_1.jsx)(BrandedTitle, { children: label }, void 0), (0, jsx_runtime_1.jsxs)(Border, { children: [(0, jsx_runtime_1.jsx)(media_preview_1.MediaPreview, { previewUrl: previewUrl, label: label }, void 0), downloadUrl && data && data.originalFilename && ((0, jsx_runtime_1.jsx)(ButtonContainer, { children: (0, jsx_runtime_1.jsxs)(akeneo_design_system_1.Button, __assign({}, buttonProps, { href: downloadUrl, download: data.originalFilename, target: "_blank" }, { children: [(0, jsx_runtime_1.jsx)(akeneo_design_system_1.DownloadIcon, {}, void 0), "Download"] }), void 0) }, void 0))] }, void 0)] }), void 0));
};
exports.FullscreenPreview = FullscreenPreview;
var templateObject_1, templateObject_2, templateObject_3;
//# sourceMappingURL=fullscreen-preview.js.map