"use strict";
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
var __spreadArray = (this && this.__spreadArray) || function (to, from, pack) {
    if (pack || arguments.length === 2) for (var i = 0, l = from.length, ar; i < l; i++) {
        if (ar || !(i in from)) {
            if (!ar) ar = Array.prototype.slice.call(from, 0, i);
            ar[i] = from[i];
        }
    }
    return to.concat(ar || Array.prototype.slice.call(from));
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.sortMeasurementFamily = exports.filterOnLabelOrCode = exports.getStandardUnitLabel = exports.getStandardUnit = exports.removeUnit = exports.addUnit = exports.setUnitOperations = exports.setUnitSymbol = exports.setUnitLabel = exports.getUnitIndex = exports.getUnit = exports.setMeasurementFamilyLabel = exports.getMeasurementFamilyLabel = void 0;
var shared_1 = require("@akeneo-pim-community/shared");
var direction_1 = require("./direction");
var unit_1 = require("./unit");
var getMeasurementFamilyLabel = function (measurementFamily, locale) {
    return (0, shared_1.getLabel)(measurementFamily.labels, locale, measurementFamily.code);
};
exports.getMeasurementFamilyLabel = getMeasurementFamilyLabel;
var setMeasurementFamilyLabel = function (measurementFamily, locale, value) {
    var _a;
    return (__assign(__assign({}, measurementFamily), { labels: __assign(__assign({}, measurementFamily.labels), (_a = {}, _a[locale] = value, _a)) }));
};
exports.setMeasurementFamilyLabel = setMeasurementFamilyLabel;
var setUnitLabel = function (measurementFamily, unitCode, locale, value) {
    var units = measurementFamily.units.map(function (unit) {
        var _a;
        if (unitCode !== unit.code) {
            return unit;
        }
        return __assign(__assign({}, unit), { labels: __assign(__assign({}, unit.labels), (_a = {}, _a[locale] = value, _a)) });
    });
    return __assign(__assign({}, measurementFamily), { units: units });
};
exports.setUnitLabel = setUnitLabel;
var setUnitOperations = function (measurementFamily, unitCode, operations) {
    var units = measurementFamily.units.map(function (unit) {
        if (unitCode !== unit.code) {
            return unit;
        }
        return __assign(__assign({}, unit), { convert_from_standard: operations });
    });
    return __assign(__assign({}, measurementFamily), { units: units });
};
exports.setUnitOperations = setUnitOperations;
var addUnit = function (measurementFamily, unit) { return (__assign(__assign({}, measurementFamily), { units: __spreadArray(__spreadArray([], measurementFamily.units, true), [unit], false) })); };
exports.addUnit = addUnit;
var removeUnit = function (measurementFamily, unitCode) { return (__assign(__assign({}, measurementFamily), { units: measurementFamily.units.filter(function (unit) { return unit.code !== unitCode; }) })); };
exports.removeUnit = removeUnit;
var setUnitSymbol = function (measurementFamily, unitCode, value) {
    var units = measurementFamily.units.map(function (unit) {
        if (unitCode !== unit.code) {
            return unit;
        }
        return __assign(__assign({}, unit), { symbol: value });
    });
    return __assign(__assign({}, measurementFamily), { units: units });
};
exports.setUnitSymbol = setUnitSymbol;
var getUnit = function (measurementFamily, unitCode) {
    return measurementFamily.units.find(function (unit) { return unit.code === unitCode; });
};
exports.getUnit = getUnit;
var getUnitIndex = function (measurementFamily, unitCode) {
    var unit = getUnit(measurementFamily, unitCode);
    if (undefined === unit)
        return -1;
    return measurementFamily.units.indexOf(unit);
};
exports.getUnitIndex = getUnitIndex;
var getStandardUnit = function (measurementFamily) {
    var unit = getUnit(measurementFamily, measurementFamily.standard_unit_code);
    if (undefined === unit)
        throw Error('Measurement family should always have a standard unit');
    return unit;
};
exports.getStandardUnit = getStandardUnit;
var getStandardUnitLabel = function (measurementFamily, locale) {
    return (0, unit_1.getUnitLabel)(getStandardUnit(measurementFamily), locale);
};
exports.getStandardUnitLabel = getStandardUnitLabel;
var filterOnLabelOrCode = function (searchValue, locale) {
    return function (entity) {
        return -1 !== entity.code.toLowerCase().indexOf(searchValue.toLowerCase()) ||
            (undefined !== entity.labels[locale] &&
                -1 !== entity.labels[locale].toLowerCase().indexOf(searchValue.toLowerCase()));
    };
};
exports.filterOnLabelOrCode = filterOnLabelOrCode;
var sortMeasurementFamily = function (sortDirection, locale, sortColumn) {
    return function (first, second) {
        var directionInverter = sortDirection === direction_1.Direction.Descending ? -1 : 1;
        switch (sortColumn) {
            case 'label':
                return (directionInverter *
                    getMeasurementFamilyLabel(first, locale).localeCompare(getMeasurementFamilyLabel(second, locale)));
            case 'code':
                return directionInverter * first.code.localeCompare(second.code);
            case 'standard_unit':
                return (directionInverter * getStandardUnitLabel(first, locale).localeCompare(getStandardUnitLabel(second, locale)));
            case 'unit_count':
                return directionInverter * (first.units.length - second.units.length);
            default:
                return 0;
        }
    };
};
exports.sortMeasurementFamily = sortMeasurementFamily;
//# sourceMappingURL=measurement-family.js.map