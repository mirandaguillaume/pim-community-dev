"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.useLocalizedNumber = void 0;
var number_1 = require("../tools/number");
var shared_1 = require("@akeneo-pim-community/shared");
var useLocalizedNumber = function () {
    var decimalSeparator = (0, shared_1.useUserContext)().get('ui_locale_decimal_separator');
    return [(0, number_1.formatNumber)(decimalSeparator), (0, number_1.unformatNumber)(decimalSeparator)];
};
exports.useLocalizedNumber = useLocalizedNumber;
//# sourceMappingURL=use-localized-number.js.map