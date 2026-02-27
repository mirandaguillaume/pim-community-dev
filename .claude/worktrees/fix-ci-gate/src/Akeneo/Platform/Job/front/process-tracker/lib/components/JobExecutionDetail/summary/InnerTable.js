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
var __rest = (this && this.__rest) || function (s, e) {
    var t = {};
    for (var p in s) if (Object.prototype.hasOwnProperty.call(s, p) && e.indexOf(p) < 0)
        t[p] = s[p];
    if (s != null && typeof Object.getOwnPropertySymbols === "function")
        for (var i = 0, p = Object.getOwnPropertySymbols(s); i < p.length; i++) {
            if (e.indexOf(p[i]) < 0 && Object.prototype.propertyIsEnumerable.call(s, p[i]))
                t[p[i]] = s[p[i]];
        }
    return t;
};
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.InnerTable = void 0;
var react_1 = __importDefault(require("react"));
var styled_components_1 = __importDefault(require("styled-components"));
var akeneo_design_system_1 = require("akeneo-design-system");
var TableContainer = (0, styled_components_1.default)(akeneo_design_system_1.Table)(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  tr:last-child {\n    td {\n      border: none;\n    }\n  }\n\n  td {\n    padding: 10px;\n  }\n\n  background-color: ", ";\n"], ["\n  tr:last-child {\n    td {\n      border: none;\n    }\n  }\n\n  td {\n    padding: 10px;\n  }\n\n  background-color: ", ";\n"])), (0, akeneo_design_system_1.getColor)('white'));
var InnerTable = function (_a) {
    var content = _a.content, rest = __rest(_a, ["content"]);
    return (react_1.default.createElement(TableContainer, __assign({}, rest),
        react_1.default.createElement(akeneo_design_system_1.Table.Body, null, Object.entries(content).map(function (_a) {
            var key = _a[0], value = _a[1];
            return (react_1.default.createElement(akeneo_design_system_1.Table.Row, { key: key },
                react_1.default.createElement(akeneo_design_system_1.Table.Cell, null, key),
                react_1.default.createElement(akeneo_design_system_1.Table.Cell, null, 'object' === typeof value ? JSON.stringify(value) : value)));
        }))));
};
exports.InnerTable = InnerTable;
var templateObject_1;
//# sourceMappingURL=InnerTable.js.map