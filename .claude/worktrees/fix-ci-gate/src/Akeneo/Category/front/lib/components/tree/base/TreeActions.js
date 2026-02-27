"use strict";
var __makeTemplateObject = (this && this.__makeTemplateObject) || function (cooked, raw) {
    if (Object.defineProperty) { Object.defineProperty(cooked, "raw", { value: raw }); } else { cooked.raw = raw; }
    return cooked;
};
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.TreeActions = void 0;
var jsx_runtime_1 = require("react/jsx-runtime");
var styled_components_1 = __importDefault(require("styled-components"));
var Container = styled_components_1.default.div(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  display: flex;\n  flex-direction: row;\n  align-items: center;\n  height: 100%;\n  width: 100%;\n\n  & > * {\n    margin-left: 10px;\n  }\n"], ["\n  display: flex;\n  flex-direction: row;\n  align-items: center;\n  height: 100%;\n  width: 100%;\n\n  & > * {\n    margin-left: 10px;\n  }\n"])));
var TreeActions = function (_a) {
    var children = _a.children;
    return (0, jsx_runtime_1.jsx)(Container, { children: children }, void 0);
};
exports.TreeActions = TreeActions;
var templateObject_1;
//# sourceMappingURL=TreeActions.js.map