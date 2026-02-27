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
exports.DiscoverEnrichedCategoriesInformationHelper = void 0;
var jsx_runtime_1 = require("react/jsx-runtime");
var shared_1 = require("@akeneo-pim-community/shared");
var akeneo_design_system_1 = require("akeneo-design-system");
var styled_components_1 = __importDefault(require("styled-components"));
var Container = styled_components_1.default.div(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  margin-bottom: 20px;\n"], ["\n  margin-bottom: 20px;\n"])));
var Footer = styled_components_1.default.div(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n  display: flex;\n  gap: 1em;\n"], ["\n  display: flex;\n  gap: 1em;\n"])));
var List = styled_components_1.default.ul(templateObject_3 || (templateObject_3 = __makeTemplateObject(["\n  margin: 10px 0;\n  padding: 0 0 0 20px;\n  line-height: 18px;\n  list-style: none;\n  color: ", ";\n"], ["\n  margin: 10px 0;\n  padding: 0 0 0 20px;\n  line-height: 18px;\n  list-style: none;\n  color: ", ";\n"])), (0, akeneo_design_system_1.getColor)('grey', 140));
var Item = styled_components_1.default.li(templateObject_4 || (templateObject_4 = __makeTemplateObject(["\n  ::marker {\n    content: '\\2022\\00A0\\00A0';\n  }\n"], ["\n  ::marker {\n    content: '\\\\2022\\\\00A0\\\\00A0';\n  }\n"])));
var surveyLink = 'https://www.surveymonkey.com/r/3K7JWK3';
var DiscoverEnrichedCategoriesInformationHelper = function () {
    var translate = (0, shared_1.useTranslate)();
    return ((0, jsx_runtime_1.jsx)(Container, __assign({ "data-testid": "discover-enriched-categories-information-helper" }, { children: (0, jsx_runtime_1.jsxs)(akeneo_design_system_1.Information, __assign({ illustration: (0, jsx_runtime_1.jsx)(akeneo_design_system_1.ProductCategoryIllustration, {}, void 0), title: translate('akeneo.category.discover_enriched_categories_information.title') }, { children: [(0, jsx_runtime_1.jsxs)(List, { children: [(0, jsx_runtime_1.jsx)(Item, { children: translate('akeneo.category.discover_enriched_categories_information.content.enrich_attribute_values') }, void 0), (0, jsx_runtime_1.jsx)(Item, { children: translate('akeneo.category.discover_enriched_categories_information.content.customizable_templates') }, void 0)] }, void 0), (0, jsx_runtime_1.jsxs)(Footer, { children: [(0, jsx_runtime_1.jsx)(akeneo_design_system_1.Link, __assign({ href: "https://help.akeneo.com/pim/serenity/articles/enrich-your-category.html", target: "_blank" }, { children: translate('akeneo.category.discover_enriched_categories_information.learn_more_link_label') }), void 0), (0, jsx_runtime_1.jsx)(akeneo_design_system_1.Link, __assign({ href: surveyLink, target: "_blank" }, { children: translate('akeneo.category.discover_enriched_categories_information.survey_link_label') }), void 0)] }, void 0)] }), void 0) }), void 0));
};
exports.DiscoverEnrichedCategoriesInformationHelper = DiscoverEnrichedCategoriesInformationHelper;
var templateObject_1, templateObject_2, templateObject_3, templateObject_4;
//# sourceMappingURL=DiscoverEnrichedCategoriesInformationHelper.js.map