"use strict";
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.AutomationFilter = void 0;
var react_1 = __importDefault(require("react"));
var shared_1 = require("@akeneo-pim-community/shared");
var akeneo_design_system_1 = require("akeneo-design-system");
var automationFilterValues = [
    { name: 'all', value: null },
    { name: 'yes', value: true },
    { name: 'no', value: false },
];
var AutomationFilter = function (_a) {
    var automationFilterValue = _a.automationFilterValue, onAutomationFilterChange = _a.onAutomationFilterChange;
    var translate = (0, shared_1.useTranslate)();
    var _b = (0, akeneo_design_system_1.useBooleanState)(), isDropdownOpen = _b[0], openDropdown = _b[1], closeDropdown = _b[2];
    return (react_1.default.createElement(akeneo_design_system_1.Dropdown, null,
        react_1.default.createElement(akeneo_design_system_1.SwitcherButton, { label: translate('akeneo_job_process_tracker.automation_filter.label'), onClick: openDropdown }, automationFilterValues.map(function (item) {
            return item.value === automationFilterValue &&
                translate("akeneo_job_process_tracker.automation_filter." + item.name);
        })),
        isDropdownOpen && (react_1.default.createElement(akeneo_design_system_1.Dropdown.Overlay, { onClose: closeDropdown },
            react_1.default.createElement(akeneo_design_system_1.Dropdown.Header, null,
                react_1.default.createElement(akeneo_design_system_1.Dropdown.Title, null, translate('akeneo_job_process_tracker.automation_filter.label'))),
            react_1.default.createElement(akeneo_design_system_1.Dropdown.ItemCollection, null, automationFilterValues.map(function (item) { return (react_1.default.createElement(akeneo_design_system_1.Dropdown.Item, { key: item.name },
                react_1.default.createElement(akeneo_design_system_1.Checkbox, { checked: item.value === automationFilterValue, onChange: function () { return onAutomationFilterChange(item.value); } }),
                translate("akeneo_job_process_tracker.automation_filter." + item.name))); }))))));
};
exports.AutomationFilter = AutomationFilter;
//# sourceMappingURL=AutomationFilter.js.map