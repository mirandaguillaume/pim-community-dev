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
exports.StatusFilter = void 0;
var react_1 = __importDefault(require("react"));
var akeneo_design_system_1 = require("akeneo-design-system");
var shared_1 = require("@akeneo-pim-community/shared");
var models_1 = require("../../models");
var getStatusFilterValueLabel = function (translate, statusFilterValue) {
    switch (statusFilterValue.length) {
        case 0:
            return translate('akeneo_job_process_tracker.status_filter.all');
        case 1:
            return translate("akeneo_job_process_tracker.status_filter." + statusFilterValue[0].toLowerCase());
        default:
            return translate('pim_common.selected', { itemsCount: statusFilterValue.length }, statusFilterValue.length);
    }
};
var StatusFilter = function (_a) {
    var statusFilterValue = _a.statusFilterValue, onStatusFilterChange = _a.onStatusFilterChange;
    var translate = (0, shared_1.useTranslate)();
    var _b = (0, akeneo_design_system_1.useBooleanState)(), isDropdownOpen = _b[0], openDropdown = _b[1], closeDropdown = _b[2];
    var handleStatusToggle = function (status) { return function () {
        if (statusFilterValue.includes(status)) {
            onStatusFilterChange(statusFilterValue.filter(function (currentStatus) { return currentStatus !== status; }));
        }
        else {
            onStatusFilterChange(__spreadArray(__spreadArray([], statusFilterValue, true), [status], false));
        }
    }; };
    return (react_1.default.createElement(akeneo_design_system_1.Dropdown, null,
        react_1.default.createElement(akeneo_design_system_1.SwitcherButton, { label: translate('akeneo_job_process_tracker.status_filter.label'), onClick: openDropdown }, getStatusFilterValueLabel(translate, statusFilterValue)),
        isDropdownOpen && (react_1.default.createElement(akeneo_design_system_1.Dropdown.Overlay, { onClose: closeDropdown },
            react_1.default.createElement(akeneo_design_system_1.Dropdown.Header, null,
                react_1.default.createElement(akeneo_design_system_1.Dropdown.Title, null, translate('akeneo_job_process_tracker.status_filter.label'))),
            react_1.default.createElement(akeneo_design_system_1.Dropdown.ItemCollection, null,
                react_1.default.createElement(akeneo_design_system_1.Dropdown.Item, null,
                    react_1.default.createElement(akeneo_design_system_1.Checkbox, { checked: 0 === statusFilterValue.length, onChange: function () { return onStatusFilterChange([]); } }),
                    translate('akeneo_job_process_tracker.status_filter.all')),
                models_1.AVAILABLE_JOB_STATUSES.map(function (status) { return (react_1.default.createElement(akeneo_design_system_1.Dropdown.Item, { key: status },
                    react_1.default.createElement(akeneo_design_system_1.Checkbox, { checked: statusFilterValue.includes(status), onChange: handleStatusToggle(status) }),
                    translate("akeneo_job_process_tracker.status_filter." + status.toLowerCase()))); }))))));
};
exports.StatusFilter = StatusFilter;
//# sourceMappingURL=StatusFilter.js.map