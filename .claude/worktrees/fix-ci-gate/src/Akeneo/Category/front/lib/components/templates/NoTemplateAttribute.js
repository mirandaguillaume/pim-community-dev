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
exports.NoTemplateAttribute = void 0;
var jsx_runtime_1 = require("react/jsx-runtime");
var shared_1 = require("@akeneo-pim-community/shared");
var akeneo_design_system_1 = require("akeneo-design-system");
var NoTemplateAttribute = function () {
    return ((0, jsx_runtime_1.jsxs)(akeneo_design_system_1.Placeholder, __assign({ title: (0, shared_1.translate)('akeneo.category.edition_form.template.no_attribute_title'), illustration: (0, jsx_runtime_1.jsx)(akeneo_design_system_1.AttributesIllustration, {}, void 0), size: "large" }, { children: [(0, shared_1.translate)('akeneo.category.edition_form.template.no_attribute_instructions'), (0, jsx_runtime_1.jsx)(akeneo_design_system_1.Link, __assign({ href: "https://help.akeneo.com/serenity-take-the-power-over-your-products/serenity-enrich-your-category", target: "_blank" }, { children: (0, shared_1.translate)('akeneo.category.template.learn_more') }), void 0)] }), void 0));
};
exports.NoTemplateAttribute = NoTemplateAttribute;
//# sourceMappingURL=NoTemplateAttribute.js.map