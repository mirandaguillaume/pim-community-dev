"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.createMeasurementFamilyFromForm = exports.initializeCreateMeasurementFamilyForm = void 0;
var operation_1 = require("../../../model/operation");
var initializeCreateMeasurementFamilyForm = function () {
    return Object.freeze({
        family_code: '',
        family_label: '',
        standard_unit_code: '',
        standard_unit_label: '',
        standard_unit_symbol: '',
    });
};
exports.initializeCreateMeasurementFamilyForm = initializeCreateMeasurementFamilyForm;
var createMeasurementFamilyFromForm = function (data, locale) {
    var _a, _b;
    return {
        code: data.family_code,
        labels: (_a = {},
            _a[locale] = data.family_label,
            _a),
        standard_unit_code: data.standard_unit_code,
        units: [
            {
                code: data.standard_unit_code,
                labels: (_b = {},
                    _b[locale] = data.standard_unit_label,
                    _b),
                symbol: data.standard_unit_symbol,
                convert_from_standard: [
                    {
                        operator: operation_1.Operator.MUL,
                        value: '1',
                    },
                ],
            },
        ],
        is_locked: false,
    };
};
exports.createMeasurementFamilyFromForm = createMeasurementFamilyFromForm;
//# sourceMappingURL=create-measurement-family-form.js.map