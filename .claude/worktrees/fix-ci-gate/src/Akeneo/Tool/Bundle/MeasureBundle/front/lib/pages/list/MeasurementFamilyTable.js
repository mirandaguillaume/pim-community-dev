"use strict";
var __makeTemplateObject = (this && this.__makeTemplateObject) || function (cooked, raw) {
    if (Object.defineProperty) { Object.defineProperty(cooked, "raw", { value: raw }); } else { cooked.raw = raw; }
    return cooked;
};
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.MeasurementFamilyTable = void 0;
var react_1 = __importDefault(require("react"));
var styled_components_1 = __importDefault(require("styled-components"));
var MeasurementFamilyRow_1 = require("../list/MeasurementFamilyRow");
var shared_1 = require("@akeneo-pim-community/shared");
var akeneo_design_system_1 = require("akeneo-design-system");
var SpacedTable = (0, styled_components_1.default)(akeneo_design_system_1.Table)(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  th {\n    padding-top: 15px;\n  }\n"], ["\n  th {\n    padding-top: 15px;\n  }\n"])));
var MeasurementFamilyTable = function (_a) {
    var measurementFamilies = _a.measurementFamilies, toggleSortDirection = _a.toggleSortDirection, getSortDirection = _a.getSortDirection;
    var translate = (0, shared_1.useTranslate)();
    return (react_1.default.createElement(SpacedTable, null,
        react_1.default.createElement(akeneo_design_system_1.Table.Header, { sticky: 44 },
            react_1.default.createElement(akeneo_design_system_1.Table.HeaderCell, { isSortable: true, sortDirection: getSortDirection('label'), onDirectionChange: function () { return toggleSortDirection('label'); } }, translate('pim_common.label')),
            react_1.default.createElement(akeneo_design_system_1.Table.HeaderCell, { isSortable: true, sortDirection: getSortDirection('code'), onDirectionChange: function () { return toggleSortDirection('code'); } }, translate('pim_common.code')),
            react_1.default.createElement(akeneo_design_system_1.Table.HeaderCell, { isSortable: true, sortDirection: getSortDirection('standard_unit'), onDirectionChange: function () { return toggleSortDirection('standard_unit'); } }, translate('measurements.list.header.standard_unit')),
            react_1.default.createElement(akeneo_design_system_1.Table.HeaderCell, { isSortable: true, sortDirection: getSortDirection('unit_count'), onDirectionChange: function () { return toggleSortDirection('unit_count'); } }, translate('measurements.list.header.unit_count'))),
        react_1.default.createElement(akeneo_design_system_1.Table.Body, null, measurementFamilies.map(function (measurementFamily) { return (react_1.default.createElement(MeasurementFamilyRow_1.MeasurementFamilyRow, { key: measurementFamily.code, measurementFamily: measurementFamily })); }))));
};
exports.MeasurementFamilyTable = MeasurementFamilyTable;
var templateObject_1;
//# sourceMappingURL=MeasurementFamilyTable.js.map