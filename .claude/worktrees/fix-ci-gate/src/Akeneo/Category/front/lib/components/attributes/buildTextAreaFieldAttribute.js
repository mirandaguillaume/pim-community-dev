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
exports.buildTextAreaFieldAttribute = void 0;
var jsx_runtime_1 = require("react/jsx-runtime");
var akeneo_design_system_1 = require("akeneo-design-system");
var templateAttributesFactory_1 = require("./templateAttributesFactory");
var fp_1 = require("lodash/fp");
var unMemoizedBuildTextAreaFieldAttribute = function (attribute) {
    var Component = function (_a) {
        var channel = _a.channel, locale = _a.locale, value = _a.value, onChange = _a.onChange;
        if (typeof value !== 'string') {
            return null;
        }
        return ((0, jsx_runtime_1.jsx)(akeneo_design_system_1.Field, __assign({ label: (0, templateAttributesFactory_1.getLabelFromAttribute)(attribute, locale), channel: channel.label, locale: locale }, { children: (0, jsx_runtime_1.jsx)(akeneo_design_system_1.TextAreaInput, { name: attribute.code, value: value, onChange: onChange }, void 0) }), void 0));
    };
    Component.displayName = 'TextAreaFieldAttribute';
    return Component;
};
exports.buildTextAreaFieldAttribute = (0, fp_1.memoize)(unMemoizedBuildTextAreaFieldAttribute);
//# sourceMappingURL=buildTextAreaFieldAttribute.js.map