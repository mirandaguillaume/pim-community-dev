"use strict";
var __makeTemplateObject = (this && this.__makeTemplateObject) || function (cooked, raw) {
    if (Object.defineProperty) { Object.defineProperty(cooked, "raw", { value: raw }); } else { cooked.raw = raw; }
    return cooked;
};
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.UnitRow = void 0;
var react_1 = __importDefault(require("react"));
var styled_components_1 = __importDefault(require("styled-components"));
var unit_1 = require("../../../model/unit");
var shared_1 = require("@akeneo-pim-community/shared");
var akeneo_design_system_1 = require("akeneo-design-system");
var UnitCodeContainer = styled_components_1.default.span(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  display: flex;\n  justify-content: space-between;\n  align-items: center;\n  flex: 1;\n"], ["\n  display: flex;\n  justify-content: space-between;\n  align-items: center;\n  flex: 1;\n"])));
var UnitCodeLabel = styled_components_1.default.span(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n  flex: 1;\n"], ["\n  flex: 1;\n"])));
var UnitRow = function (_a) {
    var unit = _a.unit, isStandardUnit = _a.isStandardUnit, _b = _a.isSelected, isSelected = _b === void 0 ? false : _b, _c = _a.isInvalid, isInvalid = _c === void 0 ? false : _c, onRowSelected = _a.onRowSelected;
    var translate = (0, shared_1.useTranslate)();
    var locale = (0, shared_1.useUserContext)().get('uiLocale');
    return (react_1.default.createElement(akeneo_design_system_1.Table.Row, { isSelected: isSelected, onClick: function () { return onRowSelected(unit.code); } },
        react_1.default.createElement(akeneo_design_system_1.Table.Cell, { rowTitle: true }, (0, unit_1.getUnitLabel)(unit, locale)),
        react_1.default.createElement(akeneo_design_system_1.Table.Cell, null,
            react_1.default.createElement(UnitCodeContainer, null,
                react_1.default.createElement(UnitCodeLabel, null, unit.code),
                isStandardUnit && react_1.default.createElement(akeneo_design_system_1.Badge, { level: "tertiary" }, translate('measurements.family.standard_unit')),
                isInvalid && react_1.default.createElement(akeneo_design_system_1.Pill, { level: "danger" })))));
};
exports.UnitRow = UnitRow;
var templateObject_1, templateObject_2;
//# sourceMappingURL=UnitRow.js.map