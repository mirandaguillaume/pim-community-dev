"use strict";
var __makeTemplateObject = (this && this.__makeTemplateObject) || function (cooked, raw) {
    if (Object.defineProperty) { Object.defineProperty(cooked, "raw", { value: raw }); } else { cooked.raw = raw; }
    return cooked;
};
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.NoResults = void 0;
var jsx_runtime_1 = require("react/jsx-runtime");
var styled_components_1 = __importDefault(require("styled-components"));
var akeneo_design_system_1 = require("akeneo-design-system");
var Container = styled_components_1.default.div(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  margin-top: 120px;\n  text-align: center;\n"], ["\n  margin-top: 120px;\n  text-align: center;\n"])));
var Title = styled_components_1.default.div(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n  font-size: ", ";\n  color: ", ";\n  margin-top: 5px;\n"], ["\n  font-size: ", ";\n  color: ", ";\n  margin-top: 5px;\n"])), function (_a) {
    var theme = _a.theme;
    return theme.fontSize.title;
}, function (_a) {
    var theme = _a.theme;
    return theme.color.grey140;
});
var SubTitle = styled_components_1.default.div(templateObject_3 || (templateObject_3 = __makeTemplateObject(["\n  font-size: ", ";\n  color: ", ";\n  margin-top: 15px;\n"], ["\n  font-size: ", ";\n  color: ", ";\n  margin-top: 15px;\n"])), function (_a) {
    var theme = _a.theme;
    return theme.fontSize.bigger;
}, function (_a) {
    var theme = _a.theme;
    return theme.color.grey120;
});
var NoResults = function (_a) {
    var title = _a.title, subtitle = _a.subtitle;
    return ((0, jsx_runtime_1.jsxs)(Container, { children: [(0, jsx_runtime_1.jsx)(akeneo_design_system_1.NoResultsIllustration, { size: 256 }, void 0), (0, jsx_runtime_1.jsx)(Title, { children: title }, void 0), (0, jsx_runtime_1.jsx)(SubTitle, { children: subtitle }, void 0)] }, void 0));
};
exports.NoResults = NoResults;
var templateObject_1, templateObject_2, templateObject_3;
//# sourceMappingURL=NoResults.js.map