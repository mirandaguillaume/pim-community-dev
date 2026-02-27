"use strict";
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
Object.defineProperty(exports, "__esModule", { value: true });
exports.MeasurementFamilySearchBar = void 0;
var react_1 = __importStar(require("react"));
var akeneo_design_system_1 = require("akeneo-design-system");
var shared_1 = require("@akeneo-pim-community/shared");
var MeasurementFamilySearchBar = function (_a) {
    var searchValue = _a.searchValue, onSearchChange = _a.onSearchChange, resultNumber = _a.resultNumber;
    var translate = (0, shared_1.useTranslate)();
    var inputRef = (0, react_1.useRef)(null);
    (0, akeneo_design_system_1.useAutoFocus)(inputRef);
    return (react_1.default.createElement(akeneo_design_system_1.Search, { sticky: 0, placeholder: translate('measurements.search.placeholder'), searchValue: searchValue, onSearchChange: onSearchChange, inputRef: inputRef },
        react_1.default.createElement(akeneo_design_system_1.Search.ResultCount, null, translate('pim_common.result_count', { itemsCount: resultNumber }, resultNumber))));
};
exports.MeasurementFamilySearchBar = MeasurementFamilySearchBar;
//# sourceMappingURL=MeasurementFamilySearchBar.js.map