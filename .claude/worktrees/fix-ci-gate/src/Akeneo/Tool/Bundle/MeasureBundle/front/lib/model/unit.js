"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.getUnitLabel = void 0;
var shared_1 = require("@akeneo-pim-community/shared");
var getUnitLabel = function (unit, locale) { return (0, shared_1.getLabel)(unit.labels, locale, unit.code); };
exports.getUnitLabel = getUnitLabel;
//# sourceMappingURL=unit.js.map