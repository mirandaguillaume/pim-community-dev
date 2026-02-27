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
exports.computeNewOwnPermissions = exports.computeNewEditPermissions = exports.computeNewViewPermissions = void 0;
var computeNewViewPermissions = function (originalFormData, values) {
    var newFormData = updateFormDataWithNewValues(originalFormData, values, 'view');
    var removedPermission = computeDifferencesBetweenPermissions(originalFormData, newFormData, 'view').removedPermission;
    if (removedPermission) {
        newFormData = removePermissionByType('edit', removedPermission, newFormData);
        newFormData = removePermissionByType('own', removedPermission, newFormData);
    }
    return newFormData;
};
exports.computeNewViewPermissions = computeNewViewPermissions;
var computeNewEditPermissions = function (originalFormData, values) {
    var newFormData = updateFormDataWithNewValues(originalFormData, values, 'edit');
    var _a = computeDifferencesBetweenPermissions(originalFormData, newFormData, 'edit'), removedPermission = _a.removedPermission, addedPermission = _a.addedPermission;
    if (removedPermission) {
        newFormData = removePermissionByType('own', removedPermission, newFormData);
    }
    if (addedPermission) {
        newFormData = addPermissionByType('view', addedPermission, newFormData);
    }
    return newFormData;
};
exports.computeNewEditPermissions = computeNewEditPermissions;
var computeNewOwnPermissions = function (originalFormData, values) {
    var newFormData = updateFormDataWithNewValues(originalFormData, values, 'own');
    var addedPermission = computeDifferencesBetweenPermissions(originalFormData, newFormData, 'own').addedPermission;
    if (addedPermission) {
        newFormData = addPermissionByType('view', addedPermission, newFormData);
        newFormData = addPermissionByType('edit', addedPermission, newFormData);
    }
    return newFormData;
};
exports.computeNewOwnPermissions = computeNewOwnPermissions;
var updateFormDataWithNewValues = function (originalFormData, values, type) {
    var _a;
    if (!originalFormData.permissions) {
        return originalFormData;
    }
    return __assign(__assign({}, originalFormData), { permissions: __assign(__assign({}, originalFormData.permissions), (_a = {}, _a[type] = __assign(__assign({}, originalFormData.permissions[type]), { value: values }), _a)) });
};
var removePermissionByType = function (type, permissionId, newPermissions) {
    var _a;
    var newValues = newPermissions.permissions[type].value.filter(function (permission) { return permission !== permissionId; });
    return __assign(__assign({}, newPermissions), { permissions: __assign(__assign({}, newPermissions.permissions), (_a = {}, _a[type] = __assign(__assign({}, newPermissions.permissions[type]), { value: newValues }), _a)) });
};
var addPermissionByType = function (type, permissionId, newPermissions) {
    var _a;
    var newValues = __spreadArray([], __read(newPermissions.permissions[type].value), false);
    newValues.push(permissionId);
    newValues = Array.from(new Set(newValues));
    return __assign(__assign({}, newPermissions), { permissions: __assign(__assign({}, newPermissions.permissions), (_a = {}, _a[type] = __assign(__assign({}, newPermissions.permissions[type]), { value: newValues }), _a)) });
};
var computeDifferencesBetweenPermissions = function (permissionsA, permissionsB, type) {
    return {
        removedPermission: permissionsA.permissions[type].value.filter(function (permissionId) { return !permissionsB.permissions[type].value.includes(permissionId); })[0],
        addedPermission: permissionsB.permissions[type].value.filter(function (permissionId) { return !permissionsA.permissions[type].value.includes(permissionId); })[0],
    };
};
//# sourceMappingURL=permissionsHelper.js.map