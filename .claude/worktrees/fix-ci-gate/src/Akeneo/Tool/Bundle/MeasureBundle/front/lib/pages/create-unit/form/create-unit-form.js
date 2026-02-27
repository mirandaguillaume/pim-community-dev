"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.validateCreateUnitForm = exports.createUnitFromForm = exports.initializeCreateUnitForm = void 0;
var operation_1 = require("../../../model/operation");
var initializeCreateUnitForm = function () {
    return Object.freeze({
        code: '',
        label: '',
        symbol: '',
        operations: [
            {
                operator: operation_1.Operator.MUL,
                value: '',
            },
        ],
    });
};
exports.initializeCreateUnitForm = initializeCreateUnitForm;
var createUnitFromForm = function (data, locale) {
    var _a;
    return {
        code: data.code,
        labels: (_a = {},
            _a[locale] = data.label,
            _a),
        symbol: data.symbol,
        convert_from_standard: data.operations,
    };
};
exports.createUnitFromForm = createUnitFromForm;
var validateCreateUnitForm = function (data, measurementFamily, __) {
    var unitCodes = measurementFamily.units.map(function (unit) { return unit.code.toLowerCase(); });
    if (unitCodes.includes(data.code.toLowerCase())) {
        return [
            {
                messageTemplate: 'measurements.validation.unit.code.must_be_unique',
                parameters: {},
                message: __('measurements.validation.unit.code.must_be_unique'),
                propertyPath: 'code',
                invalidValue: data.code,
            },
        ];
    }
    return [];
};
exports.validateCreateUnitForm = validateCreateUnitForm;
//# sourceMappingURL=create-unit-form.js.map