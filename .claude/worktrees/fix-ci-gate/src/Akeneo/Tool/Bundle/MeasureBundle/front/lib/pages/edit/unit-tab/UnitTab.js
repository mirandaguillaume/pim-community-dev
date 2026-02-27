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
exports.UnitTab = void 0;
var react_1 = __importStar(require("react"));
var styled_components_1 = __importDefault(require("styled-components"));
var measurement_family_1 = require("../../../model/measurement-family");
var akeneo_design_system_1 = require("akeneo-design-system");
var UnitDetails_1 = require("./UnitDetails");
var UnitRow_1 = require("./UnitRow");
var shared_1 = require("@akeneo-pim-community/shared");
var SpacedTable = (0, styled_components_1.default)(akeneo_design_system_1.Table)(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  th {\n    padding-top: 15px;\n  }\n"], ["\n  th {\n    padding-top: 15px;\n  }\n"])));
var TabContainer = styled_components_1.default.div(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n  display: flex;\n  flex-direction: column;\n  height: calc(100%);\n  width: 100%;\n"], ["\n  display: flex;\n  flex-direction: column;\n  height: calc(100%);\n  width: 100%;\n"])));
var TabColumns = styled_components_1.default.div(templateObject_3 || (templateObject_3 = __makeTemplateObject(["\n  display: flex;\n  overflow: hidden;\n"], ["\n  display: flex;\n  overflow: hidden;\n"])));
var UnitList = styled_components_1.default.div(templateObject_4 || (templateObject_4 = __makeTemplateObject(["\n  flex: 1;\n  overflow: auto;\n"], ["\n  flex: 1;\n  overflow: auto;\n"])));
var UnitTab = function (_a) {
    var measurementFamily = _a.measurementFamily, errors = _a.errors, onMeasurementFamilyChange = _a.onMeasurementFamilyChange, selectedUnitCode = _a.selectedUnitCode, selectUnitCode = _a.selectUnitCode;
    var translate = (0, shared_1.useTranslate)();
    var locale = (0, shared_1.useUserContext)().get('uiLocale');
    var _b = (0, react_1.useState)(''), searchValue = _b[0], setSearchValue = _b[1];
    var filteredUnits = measurementFamily.units.filter((0, measurement_family_1.filterOnLabelOrCode)(searchValue, locale));
    var inputRef = (0, react_1.useRef)(null);
    (0, akeneo_design_system_1.useAutoFocus)(inputRef);
    return (react_1.default.createElement(TabContainer, null,
        react_1.default.createElement(TabColumns, null,
            react_1.default.createElement(UnitList, null,
                react_1.default.createElement(akeneo_design_system_1.Search, { sticky: 0, placeholder: translate('measurements.search.placeholder'), searchValue: searchValue, onSearchChange: setSearchValue, inputRef: inputRef },
                    react_1.default.createElement(akeneo_design_system_1.Search.ResultCount, null, translate('pim_common.result_count', { itemsCount: filteredUnits.length }, filteredUnits.length))),
                0 === filteredUnits.length && (react_1.default.createElement(shared_1.NoDataSection, null,
                    react_1.default.createElement(akeneo_design_system_1.MeasurementIllustration, { size: 256 }),
                    react_1.default.createElement(shared_1.NoDataTitle, null, translate('pim_common.no_search_result')))),
                0 < filteredUnits.length && (react_1.default.createElement(SpacedTable, null,
                    react_1.default.createElement(akeneo_design_system_1.Table.Header, { sticky: 44 },
                        react_1.default.createElement(akeneo_design_system_1.Table.HeaderCell, null, translate('pim_common.label')),
                        react_1.default.createElement(akeneo_design_system_1.Table.HeaderCell, null, translate('pim_common.code'))),
                    react_1.default.createElement(akeneo_design_system_1.Table.Body, null, filteredUnits.map(function (unit, index) { return (react_1.default.createElement(UnitRow_1.UnitRow, { key: unit.code, unit: unit, isStandardUnit: unit.code === measurementFamily.standard_unit_code, isSelected: unit.code === selectedUnitCode, isInvalid: 0 < (0, shared_1.filterErrors)(errors, "[" + index + "]").length, onRowSelected: selectUnitCode })); }))))),
            react_1.default.createElement(UnitDetails_1.UnitDetails, { measurementFamily: measurementFamily, selectedUnitCode: selectedUnitCode, onMeasurementFamilyChange: onMeasurementFamilyChange, selectUnitCode: selectUnitCode, errors: (0, shared_1.filterErrors)(errors, "[" + (0, measurement_family_1.getUnitIndex)(measurementFamily, selectedUnitCode) + "]") }))));
};
exports.UnitTab = UnitTab;
var templateObject_1, templateObject_2, templateObject_3, templateObject_4;
//# sourceMappingURL=UnitTab.js.map