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
exports.buildRichTextFieldAttribute = void 0;
var jsx_runtime_1 = require("react/jsx-runtime");
var akeneo_design_system_1 = require("akeneo-design-system");
var templateAttributesFactory_1 = require("./templateAttributesFactory");
var styled_components_1 = __importDefault(require("styled-components"));
var fp_1 = require("lodash/fp");
var models_1 = require("../../models");
var Field960 = (0, styled_components_1.default)(akeneo_design_system_1.Field)(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  max-width: 960px;\n"], ["\n  max-width: 960px;\n"])));
var unMemoizedBuildRichTextFieldAttribute = function (attribute) {
    var Component = function (_a) {
        var channel = _a.channel, locale = _a.locale, value = _a.value, onChange = _a.onChange;
        if (typeof value !== 'string') {
            return null;
        }
        return ((0, jsx_runtime_1.jsx)(Field960, __assign({ label: (0, templateAttributesFactory_1.getLabelFromAttribute)(attribute, locale), channel: channel.label, locale: locale }, { children: (0, jsx_runtime_1.jsx)(akeneo_design_system_1.TextAreaInput, { isRichText: true, name: attribute.code, value: value, onChange: onChange }, (0, models_1.buildCompositeKey)(attribute, channel.code, locale)) }), void 0));
    };
    Component.displayName = 'RichTextFieldAttribute';
    return Component;
};
exports.buildRichTextFieldAttribute = (0, fp_1.memoize)(unMemoizedBuildRichTextFieldAttribute);
var templateObject_1;
//# sourceMappingURL=buildRichTextFieldAttribute.js.map