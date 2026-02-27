"use strict";
var __makeTemplateObject = (this && this.__makeTemplateObject) || function (cooked, raw) {
    if (Object.defineProperty) { Object.defineProperty(cooked, "raw", { value: raw }); } else { cooked.raw = raw; }
    return cooked;
};
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.MediaPreview = void 0;
var jsx_runtime_1 = require("react/jsx-runtime");
var styled_components_1 = __importDefault(require("styled-components"));
var akeneo_design_system_1 = require("akeneo-design-system");
var PreviewImage = (0, styled_components_1.default)(akeneo_design_system_1.Image)(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  border: none;\n  width: auto;\n  min-height: 250px;\n  max-width: 100%;\n  max-height: calc(100vh - 250px);\n"], ["\n  border: none;\n  width: auto;\n  min-height: 250px;\n  max-width: 100%;\n  max-height: calc(100vh - 250px);\n"])));
var MediaPreview = function (_a) {
    var previewUrl = _a.previewUrl, label = _a.label;
    return ((0, jsx_runtime_1.jsx)(jsx_runtime_1.Fragment, { children: (0, jsx_runtime_1.jsx)(PreviewImage, { fit: "contain", src: previewUrl, alt: label }, void 0) }, void 0));
};
exports.MediaPreview = MediaPreview;
var templateObject_1;
//# sourceMappingURL=media-preview.js.map