"use strict";
var __makeTemplateObject = (this && this.__makeTemplateObject) || function (cooked, raw) {
    if (Object.defineProperty) { Object.defineProperty(cooked, "raw", { value: raw }); } else { cooked.raw = raw; }
    return cooked;
};
var __createBinding = (this && this.__createBinding) || (Object.create ? (function(o, m, k, k2) {
    if (k2 === undefined) k2 = k;
    Object.defineProperty(o, k2, { enumerable: true, get: function() { return m[k]; } });
}) : (function(o, m, k, k2) {
    if (k2 === undefined) k2 = k;
    o[k2] = m[k];
}));
var __setModuleDefault = (this && this.__setModuleDefault) || (Object.create ? (function(o, v) {
    Object.defineProperty(o, "default", { enumerable: true, value: v });
}) : function(o, v) {
    o["default"] = v;
});
var __importStar = (this && this.__importStar) || function (mod) {
    if (mod && mod.__esModule) return mod;
    var result = {};
    if (mod != null) for (var k in mod) if (k !== "default" && Object.prototype.hasOwnProperty.call(mod, k)) __createBinding(result, mod, k);
    __setModuleDefault(result, mod);
    return result;
};
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.WarningHelper = void 0;
var react_1 = __importStar(require("react"));
var styled_components_1 = __importDefault(require("styled-components"));
var akeneo_design_system_1 = require("akeneo-design-system");
var shared_1 = require("@akeneo-pim-community/shared");
var InnerTable_1 = require("./InnerTable");
var SpacedTable = (0, styled_components_1.default)(InnerTable_1.InnerTable)(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  margin-top: 10px;\n"], ["\n  margin-top: 10px;\n"])));
var WarningHelper = function (_a) {
    var warning = _a.warning;
    var translate = (0, shared_1.useTranslate)();
    var _b = (0, react_1.useState)(false), isExpanded = _b[0], setExpanded = _b[1];
    var toggleExpanded = function () { return setExpanded(!isExpanded); };
    var itemIsFilled = 0 < Object.keys(warning.item).length;
    return (react_1.default.createElement(akeneo_design_system_1.Helper, { level: "warning" },
        react_1.default.createElement("ul", null, warning.reason.split('\n').map(function (reason, index) { return (react_1.default.createElement("li", { key: index }, reason)); })),
        itemIsFilled && (react_1.default.createElement(akeneo_design_system_1.Link, { onClick: toggleExpanded }, translate(isExpanded ? 'job_execution.summary.hide_item' : 'job_execution.summary.display_item'))),
        isExpanded && react_1.default.createElement(SpacedTable, { content: warning.item })));
};
exports.WarningHelper = WarningHelper;
var templateObject_1;
//# sourceMappingURL=WarningHelper.js.map