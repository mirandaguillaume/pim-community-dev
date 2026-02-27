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
exports.UnitDetails = void 0;
var react_1 = __importStar(require("react"));
var styled_components_1 = __importDefault(require("styled-components"));
var OperationCollection_1 = require("../../common/OperationCollection");
var measurement_family_1 = require("../../../model/measurement-family");
var use_ui_locales_1 = require("../../../shared/hooks/use-ui-locales");
var unit_1 = require("../../../model/unit");
var shared_1 = require("@akeneo-pim-community/shared");
var akeneo_design_system_1 = require("akeneo-design-system");
var Container = styled_components_1.default.div(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  margin-left: 40px;\n  width: 400px;\n  overflow: auto;\n"], ["\n  margin-left: 40px;\n  width: 400px;\n  overflow: auto;\n"])));
var Footer = styled_components_1.default.div(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n  background: ", ";\n  border-top: 1px solid ", ";\n  padding: 10px 0 40px;\n  position: sticky;\n  bottom: 0;\n  display: flex;\n  justify-content: flex-end;\n  z-index: 10;\n"], ["\n  background: ", ";\n  border-top: 1px solid ", ";\n  padding: 10px 0 40px;\n  position: sticky;\n  bottom: 0;\n  display: flex;\n  justify-content: flex-end;\n  z-index: 10;\n"])), (0, akeneo_design_system_1.getColor)('white'), (0, akeneo_design_system_1.getColor)('grey', 80));
var UnitDetails = function (_a) {
    var measurementFamily = _a.measurementFamily, selectedUnitCode = _a.selectedUnitCode, onMeasurementFamilyChange = _a.onMeasurementFamilyChange, selectUnitCode = _a.selectUnitCode, errors = _a.errors;
    var translate = (0, shared_1.useTranslate)();
    var isGranted = (0, shared_1.useSecurity)().isGranted;
    var locales = (0, use_ui_locales_1.useUiLocales)();
    var locale = (0, shared_1.useUserContext)().get('uiLocale');
    var selectedUnit = (0, measurement_family_1.getUnit)(measurementFamily, selectedUnitCode);
    var _b = (0, akeneo_design_system_1.useBooleanState)(false), isConfirmDeleteUnitModalOpen = _b[0], openConfirmDeleteUnitModal = _b[1], closeConfirmDeleteUnitModal = _b[2];
    var handleRemoveUnit = (0, react_1.useCallback)(function () {
        onMeasurementFamilyChange((0, measurement_family_1.removeUnit)(measurementFamily, selectedUnitCode));
        selectUnitCode(measurementFamily.standard_unit_code);
        closeConfirmDeleteUnitModal();
    }, [closeConfirmDeleteUnitModal, measurementFamily, selectedUnitCode, onMeasurementFamilyChange, selectUnitCode]);
    if (undefined === selectedUnit)
        return null;
    return (react_1.default.createElement(react_1.default.Fragment, null,
        isConfirmDeleteUnitModalOpen && (react_1.default.createElement(shared_1.DeleteModal, { title: translate('measurements.title.measurement'), onConfirm: handleRemoveUnit, onCancel: closeConfirmDeleteUnitModal }, translate('measurements.unit.delete.confirm'))),
        react_1.default.createElement(Container, null,
            react_1.default.createElement(shared_1.Section, null,
                react_1.default.createElement(akeneo_design_system_1.SectionTitle, { sticky: 0 },
                    react_1.default.createElement(akeneo_design_system_1.SectionTitle.Title, null, translate('measurements.unit.title', { unitLabel: (0, unit_1.getUnitLabel)(selectedUnit, locale) }))),
                react_1.default.createElement(shared_1.TextField, { label: translate('pim_common.code'), value: selectedUnit.code, required: true, readOnly: true, errors: (0, shared_1.filterErrors)(errors, '[code]') }),
                react_1.default.createElement(shared_1.TextField, { label: translate('measurements.unit.symbol'), value: selectedUnit.symbol, readOnly: !isGranted('akeneo_measurements_measurement_unit_edit'), onChange: function (value) { return onMeasurementFamilyChange((0, measurement_family_1.setUnitSymbol)(measurementFamily, selectedUnit.code, value)); }, errors: (0, shared_1.filterErrors)(errors, '[symbol]') }),
                react_1.default.createElement(OperationCollection_1.OperationCollection, { operations: selectedUnit.convert_from_standard, readOnly: !isGranted('akeneo_measurements_measurement_unit_edit') ||
                        measurementFamily.is_locked ||
                        selectedUnit.code === measurementFamily.standard_unit_code, onOperationsChange: function (operations) {
                        return onMeasurementFamilyChange((0, measurement_family_1.setUnitOperations)(measurementFamily, selectedUnit.code, operations));
                    }, errors: (0, shared_1.filterErrors)(errors, '[convert_from_standard]') }),
                react_1.default.createElement(akeneo_design_system_1.SectionTitle, { sticky: 0 },
                    react_1.default.createElement(akeneo_design_system_1.SectionTitle.Title, null, translate('measurements.label_translations'))),
                null !== locales &&
                    locales.map(function (locale) { return (react_1.default.createElement(shared_1.TextField, { label: locale.label, key: locale.code, locale: locale.code, readOnly: !isGranted('akeneo_measurements_measurement_unit_edit'), value: selectedUnit.labels[locale.code] || '', onChange: function (value) {
                            return onMeasurementFamilyChange((0, measurement_family_1.setUnitLabel)(measurementFamily, selectedUnitCode, locale.code, value));
                        }, errors: (0, shared_1.filterErrors)(errors, "[labels][" + locale.code + "]") })); })),
            isGranted('akeneo_measurements_measurement_unit_delete') &&
                !measurementFamily.is_locked &&
                selectedUnitCode !== measurementFamily.standard_unit_code && (react_1.default.createElement(Footer, null,
                react_1.default.createElement(akeneo_design_system_1.Button, { level: "danger", ghost: true, onClick: openConfirmDeleteUnitModal }, translate('measurements.unit.delete.button')))))));
};
exports.UnitDetails = UnitDetails;
var templateObject_1, templateObject_2;
//# sourceMappingURL=UnitDetails.js.map