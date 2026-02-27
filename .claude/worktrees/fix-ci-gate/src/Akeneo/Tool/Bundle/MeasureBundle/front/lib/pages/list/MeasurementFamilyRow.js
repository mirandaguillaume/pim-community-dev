"use strict";
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.MeasurementFamilyRow = void 0;
var react_1 = __importDefault(require("react"));
var react_router_dom_1 = require("react-router-dom");
var measurement_family_1 = require("../../model/measurement-family");
var shared_1 = require("@akeneo-pim-community/shared");
var akeneo_design_system_1 = require("akeneo-design-system");
var MeasurementFamilyRow = function (_a) {
    var measurementFamily = _a.measurementFamily;
    var locale = (0, shared_1.useUserContext)().get('uiLocale');
    var history = (0, react_router_dom_1.useHistory)();
    var measurementFamilyLabel = (0, measurement_family_1.getMeasurementFamilyLabel)(measurementFamily, locale);
    return (react_1.default.createElement(akeneo_design_system_1.Table.Row, { onClick: function () { return history.push("/" + measurementFamily.code); } },
        react_1.default.createElement(akeneo_design_system_1.Table.Cell, { rowTitle: true }, measurementFamilyLabel),
        react_1.default.createElement(akeneo_design_system_1.Table.Cell, null, measurementFamily.code),
        react_1.default.createElement(akeneo_design_system_1.Table.Cell, null, (0, measurement_family_1.getStandardUnitLabel)(measurementFamily, locale)),
        react_1.default.createElement(akeneo_design_system_1.Table.Cell, null, measurementFamily.units.length)));
};
exports.MeasurementFamilyRow = MeasurementFamilyRow;
//# sourceMappingURL=MeasurementFamilyRow.js.map