"use strict";
var __spreadArray = (this && this.__spreadArray) || function (to, from, pack) {
    if (pack || arguments.length === 2) for (var i = 0, l = from.length, ar; i < l; i++) {
        if (ar || !(i in from)) {
            if (!ar) ar = Array.prototype.slice.call(from, 0, i);
            ar[i] = from[i];
        }
    }
    return to.concat(ar || Array.prototype.slice.call(from));
};
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.TypeFilter = void 0;
var react_1 = __importDefault(require("react"));
var akeneo_design_system_1 = require("akeneo-design-system");
var shared_1 = require("@akeneo-pim-community/shared");
var useJobExecutionTypes_1 = require("../../hooks/useJobExecutionTypes");
var getStatusFilterValueLabel = function (translate, typeFilterValue) {
    switch (typeFilterValue.length) {
        case 0:
            return translate('akeneo_job_process_tracker.type_filter.all');
        case 1:
            return translate("akeneo_job_process_tracker.type_filter." + typeFilterValue[0]);
        default:
            return translate('pim_common.selected', { itemsCount: typeFilterValue.length }, typeFilterValue.length);
    }
};
var TypeFilter = function (_a) {
    var typeFilterValue = _a.typeFilterValue, onTypeFilterChange = _a.onTypeFilterChange;
    var translate = (0, shared_1.useTranslate)();
    var _b = (0, akeneo_design_system_1.useBooleanState)(false), isOpen = _b[0], open = _b[1], close = _b[2];
    var types = (0, useJobExecutionTypes_1.useJobExecutionTypes)();
    return (react_1.default.createElement(akeneo_design_system_1.Dropdown, null,
        react_1.default.createElement(akeneo_design_system_1.SwitcherButton, { onClick: open, label: translate('akeneo_job_process_tracker.type_filter.label') }, getStatusFilterValueLabel(translate, typeFilterValue)),
        isOpen && (react_1.default.createElement(akeneo_design_system_1.Dropdown.Overlay, { verticalPosition: "down", onClose: close },
            react_1.default.createElement(akeneo_design_system_1.Dropdown.Header, null,
                react_1.default.createElement(akeneo_design_system_1.Dropdown.Title, null, translate('akeneo_job_process_tracker.type_filter.label'))),
            react_1.default.createElement(akeneo_design_system_1.Dropdown.ItemCollection, null,
                react_1.default.createElement(akeneo_design_system_1.Dropdown.Item, null,
                    react_1.default.createElement(akeneo_design_system_1.Checkbox, { checked: 0 === typeFilterValue.length, onChange: function () { return onTypeFilterChange([]); } }),
                    translate('akeneo_job_process_tracker.type_filter.all')),
                types &&
                    types.map(function (type) { return (react_1.default.createElement(akeneo_design_system_1.Dropdown.Item, { key: type },
                        react_1.default.createElement(akeneo_design_system_1.Checkbox, { checked: typeFilterValue.includes(type), onChange: function (checked) {
                                if (checked) {
                                    onTypeFilterChange(__spreadArray(__spreadArray([], typeFilterValue, true), [type], false));
                                }
                                else {
                                    onTypeFilterChange(typeFilterValue.filter(function (typeFilterValueType) { return typeFilterValueType !== type; }));
                                }
                            } }),
                        translate("akeneo_job_process_tracker.type_filter." + type))); }))))));
};
exports.TypeFilter = TypeFilter;
//# sourceMappingURL=TypeFilter.js.map