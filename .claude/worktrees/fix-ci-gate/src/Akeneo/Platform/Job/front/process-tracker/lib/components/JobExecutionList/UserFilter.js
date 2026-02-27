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
exports.UserFilter = void 0;
var react_1 = __importStar(require("react"));
var akeneo_design_system_1 = require("akeneo-design-system");
var shared_1 = require("@akeneo-pim-community/shared");
var useJobExecutionUsers_1 = require("../../hooks/useJobExecutionUsers");
var getUserFilterValueLabel = function (translate, userFilterValue) {
    switch (userFilterValue.length) {
        case 0:
            return translate('akeneo_job_process_tracker.user_filter.all');
        case 1:
            return userFilterValue[0];
        default:
            return translate('pim_common.selected', { itemsCount: userFilterValue.length }, userFilterValue.length);
    }
};
var UserFilter = function (_a) {
    var userFilterValue = _a.userFilterValue, onUserFilterChange = _a.onUserFilterChange;
    var translate = (0, shared_1.useTranslate)();
    var _b = (0, akeneo_design_system_1.useBooleanState)(false), isOpen = _b[0], open = _b[1], close = _b[2];
    var _c = (0, react_1.useState)(''), searchValue = _c[0], setSearchValue = _c[1];
    var users = (0, useJobExecutionUsers_1.useJobExecutionUsers)();
    return (react_1.default.createElement(akeneo_design_system_1.Dropdown, null,
        react_1.default.createElement(akeneo_design_system_1.SwitcherButton, { onClick: open, label: translate('akeneo_job_process_tracker.user_filter.label') }, getUserFilterValueLabel(translate, userFilterValue)),
        isOpen && (react_1.default.createElement(akeneo_design_system_1.Dropdown.Overlay, { verticalPosition: "down", onClose: close },
            react_1.default.createElement(akeneo_design_system_1.Dropdown.Header, null,
                react_1.default.createElement(akeneo_design_system_1.Search, { onSearchChange: setSearchValue, placeholder: translate('akeneo_job_process_tracker.user_filter.search'), searchValue: searchValue, title: translate('akeneo_job_process_tracker.user_filter.search') })),
            react_1.default.createElement(akeneo_design_system_1.Dropdown.ItemCollection, null,
                react_1.default.createElement(akeneo_design_system_1.Dropdown.Item, null,
                    react_1.default.createElement(akeneo_design_system_1.Checkbox, { checked: 0 === userFilterValue.length, onChange: function () { return onUserFilterChange([]); } }),
                    translate('akeneo_job_process_tracker.user_filter.all')),
                users &&
                    users
                        .filter(function (user) { return user.indexOf(searchValue) !== -1; })
                        .map(function (user) { return (react_1.default.createElement(akeneo_design_system_1.Dropdown.Item, { key: user },
                        react_1.default.createElement(akeneo_design_system_1.Checkbox, { checked: userFilterValue.includes(user), onChange: function (checked) {
                                if (checked) {
                                    onUserFilterChange(__spreadArray(__spreadArray([], userFilterValue, true), [user], false));
                                }
                                else {
                                    onUserFilterChange(userFilterValue.filter(function (userFilterValueType) { return userFilterValueType !== user; }));
                                }
                            } }),
                        user)); }))))));
};
exports.UserFilter = UserFilter;
//# sourceMappingURL=UserFilter.js.map