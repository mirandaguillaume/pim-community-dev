"use strict";
var __makeTemplateObject = (this && this.__makeTemplateObject) || function (cooked, raw) {
    if (Object.defineProperty) { Object.defineProperty(cooked, "raw", { value: raw }); } else { cooked.raw = raw; }
    return cooked;
};
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.PropertyTab = void 0;
var react_1 = __importDefault(require("react"));
var styled_components_1 = __importDefault(require("styled-components"));
var akeneo_design_system_1 = require("akeneo-design-system");
var measurement_family_1 = require("../../model/measurement-family");
var use_ui_locales_1 = require("../../shared/hooks/use-ui-locales");
var shared_1 = require("@akeneo-pim-community/shared");
var Container = styled_components_1.default.div(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  display: flex;\n  flex-direction: column;\n  flex: 1;\n  overflow: auto;\n"], ["\n  display: flex;\n  flex-direction: column;\n  flex: 1;\n  overflow: auto;\n"])));
var PropertyTab = function (_a) {
    var measurementFamily = _a.measurementFamily, errors = _a.errors, onMeasurementFamilyChange = _a.onMeasurementFamilyChange;
    var translate = (0, shared_1.useTranslate)();
    var locales = (0, use_ui_locales_1.useUiLocales)();
    var isGranted = (0, shared_1.useSecurity)().isGranted;
    return (react_1.default.createElement(Container, null,
        react_1.default.createElement(shared_1.Section, null,
            react_1.default.createElement(akeneo_design_system_1.SectionTitle, { sticky: 0 },
                react_1.default.createElement(akeneo_design_system_1.SectionTitle.Title, null, translate('pim_common.general_properties'))),
            react_1.default.createElement(shared_1.TextField, { label: translate('pim_common.code'), value: measurementFamily.code, errors: (0, shared_1.filterErrors)(errors, 'code'), required: true, readOnly: true }),
            react_1.default.createElement(akeneo_design_system_1.SectionTitle, { sticky: 0 },
                react_1.default.createElement(akeneo_design_system_1.SectionTitle.Title, null, translate('measurements.label_translations'))),
            null !== locales &&
                locales.map(function (locale, index) { return (react_1.default.createElement(shared_1.TextField, { autoFocus: 0 === index, label: locale.label, errors: (0, shared_1.filterErrors)(errors, "labels[" + locale.code + "]"), key: locale.code, locale: locale.code, readOnly: !isGranted('akeneo_measurements_measurement_family_edit_properties'), value: measurementFamily.labels[locale.code] || '', onChange: function (value) {
                        return onMeasurementFamilyChange((0, measurement_family_1.setMeasurementFamilyLabel)(measurementFamily, locale.code, value));
                    } })); }))));
};
exports.PropertyTab = PropertyTab;
var templateObject_1;
//# sourceMappingURL=PropertyTab.js.map