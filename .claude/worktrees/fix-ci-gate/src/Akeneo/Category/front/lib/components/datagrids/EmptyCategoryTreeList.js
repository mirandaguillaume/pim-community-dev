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
exports.EmptyCategoryTreeList = void 0;
var jsx_runtime_1 = require("react/jsx-runtime");
var styled_components_1 = __importDefault(require("styled-components"));
var akeneo_design_system_1 = require("akeneo-design-system");
var shared_1 = require("@akeneo-pim-community/shared");
var Container = styled_components_1.default.div(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  display: flex;\n  align-items: center;\n  justify-content: center;\n  flex-direction: column;\n  height: 100%;\n"], ["\n  display: flex;\n  align-items: center;\n  justify-content: center;\n  flex-direction: column;\n  height: 100%;\n"])));
var Title = styled_components_1.default.div(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n  font-size: ", ";\n  color: ", ";\n  margin-top: 5px;\n"], ["\n  font-size: ", ";\n  color: ", ";\n  margin-top: 5px;\n"])), (0, akeneo_design_system_1.getFontSize)('title'), (0, akeneo_design_system_1.getColor)('grey', 140));
var Hint = styled_components_1.default.div(templateObject_3 || (templateObject_3 = __makeTemplateObject(["\n  font-size: ", ";\n  color: ", ";\n  margin-top: 5px;\n  height: 18px;\n  text-align: center;\n"], ["\n  font-size: ", ";\n  color: ", ";\n  margin-top: 5px;\n  height: 18px;\n  text-align: center;\n"])), (0, akeneo_design_system_1.getFontSize)('big'), (0, akeneo_design_system_1.getColor)('brand', 100));
var EmptyCategoryTreeList = function () {
    var translate = (0, shared_1.useTranslate)();
    return ((0, jsx_runtime_1.jsxs)(Container, { children: [(0, jsx_runtime_1.jsx)(akeneo_design_system_1.ProductCategoryIllustration, {}, void 0), (0, jsx_runtime_1.jsx)(Title, { children: translate('pim_enrich.entity.category.content.empty_tree_list.title') }, void 0), (0, jsx_runtime_1.jsx)(Hint, { children: (0, jsx_runtime_1.jsx)(akeneo_design_system_1.Link, __assign({ href: "https://help.akeneo.com/pim/serenity/articles/what-is-a-category.html#how-to-create-a-new-category", target: "_blank" }, { children: translate('pim_enrich.entity.category.content.empty_tree_list.hint') }), void 0) }, void 0)] }, void 0));
};
exports.EmptyCategoryTreeList = EmptyCategoryTreeList;
var templateObject_1, templateObject_2, templateObject_3;
//# sourceMappingURL=EmptyCategoryTreeList.js.map