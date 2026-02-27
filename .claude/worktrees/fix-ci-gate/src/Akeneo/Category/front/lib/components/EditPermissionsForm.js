"use strict";
var __makeTemplateObject = (this && this.__makeTemplateObject) || function (cooked, raw) {
    if (Object.defineProperty) { Object.defineProperty(cooked, "raw", { value: raw }); } else { cooked.raw = raw; }
    return cooked;
};
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
var __read = (this && this.__read) || function (o, n) {
    var m = typeof Symbol === "function" && o[Symbol.iterator];
    if (!m) return o;
    var i = m.call(o), r, ar = [], e;
    try {
        while ((n === void 0 || n-- > 0) && !(r = i.next()).done) ar.push(r.value);
    }
    catch (error) { e = { error: error }; }
    finally {
        try {
            if (r && !r.done && (m = i["return"])) m.call(i);
        }
        finally { if (e) throw e.error; }
    }
    return ar;
};
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.EditPermissionsForm = void 0;
var jsx_runtime_1 = require("react/jsx-runtime");
var shared_1 = require("@akeneo-pim-community/shared");
var akeneo_design_system_1 = require("akeneo-design-system");
var react_1 = require("react");
var styled_components_1 = __importDefault(require("styled-components"));
var useFetchUserGroups_1 = require("../hooks/useFetchUserGroups");
var FormContainer = styled_components_1.default.div(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  margin-top: 20px;\n\n  & > * {\n    margin: 0 10px 20px 0;\n  }\n"], ["\n  margin-top: 20px;\n\n  & > * {\n    margin: 0 10px 20px 0;\n  }\n"])));
var PermissionField = (0, styled_components_1.default)(akeneo_design_system_1.Field)(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n  max-width: 400px;\n"], ["\n  max-width: 400px;\n"])));
var EditPermissionsForm = function (_a) {
    var category = _a.category, applyPermissionsOnChildren = _a.applyPermissionsOnChildren, onChangePermissions = _a.onChangePermissions, onChangeApplyPermissionsOnChildren = _a.onChangeApplyPermissionsOnChildren;
    var translate = (0, shared_1.useTranslate)();
    var _b = (0, useFetchUserGroups_1.useFetchUserGroups)(), fetchedUserGroups = _b.data, userGroupStatus = _b.status;
    var _c = __read((0, react_1.useState)(null), 2), userGroups = _c[0], setUserGroup = _c[1];
    (0, react_1.useEffect)(function () {
        if (userGroupStatus === 'success') {
            if (fetchedUserGroups) {
                setUserGroup(fetchedUserGroups);
            }
        }
    }, [fetchedUserGroups, userGroupStatus]);
    var handleChangePermissions = (0, react_1.useCallback)(function (type) { return function (values) {
        if (userGroups) {
            onChangePermissions(userGroups, type, values.map(function (value) { return parseInt(value, 10); }));
        }
    }; }, [onChangePermissions, userGroups]);
    var makeGroupOptions = (0, react_1.useCallback)(function (type) {
        if (userGroups) {
            return userGroups === null || userGroups === void 0 ? void 0 : userGroups.map(function (_a) {
                var id = _a.id, label = _a.label;
                return ((0, jsx_runtime_1.jsx)(akeneo_design_system_1.MultiSelectInput.Option, __assign({ value: id.toString() }, { children: label }), type + "-" + id));
            });
        }
        return [];
    }, [userGroups]);
    if (!category.permissions) {
        return (0, jsx_runtime_1.jsx)(jsx_runtime_1.Fragment, {}, void 0);
    }
    var groupOptions = {
        view: makeGroupOptions('view'),
        edit: makeGroupOptions('edit'),
        own: makeGroupOptions('own'),
    };
    var extractPermissionIdAsString = function (permissions) {
        return permissions === null || permissions === void 0 ? void 0 : permissions.map(function (permission) { var _a; return (_a = permission.id) === null || _a === void 0 ? void 0 : _a.toString(); });
    };
    return ((0, jsx_runtime_1.jsxs)(FormContainer, { children: [(0, jsx_runtime_1.jsx)(PermissionField, __assign({ label: translate('category.permissions.view.label') }, { children: (0, jsx_runtime_1.jsx)(akeneo_design_system_1.MultiSelectInput, __assign({ readOnly: false, value: extractPermissionIdAsString(category.permissions.view), name: "view-permission", emptyResultLabel: translate('pim_common.no_result'), openLabel: translate('pim_common.open'), removeLabel: translate('pim_common.remove'), onChange: handleChangePermissions('view') }, { children: groupOptions['view'] }), void 0) }), void 0), (0, jsx_runtime_1.jsx)(PermissionField, __assign({ label: translate('category.permissions.edit.label') }, { children: (0, jsx_runtime_1.jsx)(akeneo_design_system_1.MultiSelectInput, __assign({ value: extractPermissionIdAsString(category.permissions.edit), name: "edit-permission", emptyResultLabel: translate('pim_common.no_result'), openLabel: translate('pim_common.open'), removeLabel: translate('pim_common.remove'), onChange: handleChangePermissions('edit') }, { children: groupOptions['edit'] }), void 0) }), void 0), (0, jsx_runtime_1.jsxs)(PermissionField, __assign({ label: translate('category.permissions.own.label') }, { children: [(0, jsx_runtime_1.jsx)(akeneo_design_system_1.MultiSelectInput, __assign({ value: extractPermissionIdAsString(category.permissions.own), name: "own-permission", emptyResultLabel: translate('pim_common.no_result'), openLabel: translate('pim_common.open'), removeLabel: translate('pim_common.remove'), onChange: handleChangePermissions('own') }, { children: groupOptions['own'] }), void 0), (0, jsx_runtime_1.jsx)(akeneo_design_system_1.Helper, __assign({ level: "info" }, { children: translate('category.permissions.own.help') }), void 0)] }), void 0), (0, jsx_runtime_1.jsxs)(akeneo_design_system_1.Field, __assign({ label: translate('category.permissions.apply_on_children.label') }, { children: [(0, jsx_runtime_1.jsx)(akeneo_design_system_1.BooleanInput, { clearable: false, readOnly: false, value: applyPermissionsOnChildren, noLabel: translate('pim_common.no'), yesLabel: translate('pim_common.yes'), onChange: onChangeApplyPermissionsOnChildren }, void 0), (0, jsx_runtime_1.jsx)(akeneo_design_system_1.Helper, __assign({ level: "info" }, { children: translate('category.permissions.apply_on_children.help') }), void 0)] }), void 0)] }, void 0));
};
exports.EditPermissionsForm = EditPermissionsForm;
var templateObject_1, templateObject_2;
//# sourceMappingURL=EditPermissionsForm.js.map