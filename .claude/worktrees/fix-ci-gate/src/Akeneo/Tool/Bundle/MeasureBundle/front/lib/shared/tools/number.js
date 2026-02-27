"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.formatNumber = exports.unformatNumber = void 0;
var STANDARD_DECIMAL_SEPARATOR = '.';
var unformatNumber = function (decimalSeparator) {
    return function (numberToUnformat) {
        decimalSeparator = decimalSeparator || STANDARD_DECIMAL_SEPARATOR;
        var result = numberToUnformat.replace(/\s*/g, '');
        if (STANDARD_DECIMAL_SEPARATOR !== decimalSeparator) {
            result = result.replace(new RegExp(decimalSeparator, 'g'), STANDARD_DECIMAL_SEPARATOR);
        }
        return result.replace(/[a-z]*[A-Z]*\s*/g, '');
    };
};
exports.unformatNumber = unformatNumber;
var formatNumber = function (decimalSeparator) {
    return function (number) {
        return number.replace(new RegExp('\\' + STANDARD_DECIMAL_SEPARATOR, 'g'), decimalSeparator || STANDARD_DECIMAL_SEPARATOR);
    };
};
exports.formatNumber = formatNumber;
//# sourceMappingURL=number.js.map