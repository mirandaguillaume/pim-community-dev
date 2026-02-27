"use strict";
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
exports.alterPermissionsConsistently = exports.ensureSuperset = exports.ensureSubset = void 0;
var fp_1 = require("lodash/fp");
function ensureSubset(permissions1, permission2) {
    return (0, fp_1.filter)(function (permission) { return permission2.map(function (permission) { return permission.id; }).includes(permission.id); }, permissions1);
}
exports.ensureSubset = ensureSubset;
function ensureSuperset(permissions1, permissions2) {
    return __spreadArray(__spreadArray([], __read(permissions1), false), __read((0, fp_1.filter)(function (permission) { return !permissions1.map(function (permission) { return permission.id; }).includes(permission.id); }, permissions2)), false);
}
exports.ensureSuperset = ensureSuperset;
function alterPermissionsConsistently(userGroups, permissions, changes) {
    var type = changes.type, values = changes.values;
    var consistentPermissions = (0, fp_1.cloneDeep)(permissions);
    consistentPermissions[type] = userGroups
        .filter(function (userGroup) { return values.includes(parseInt(userGroup.id, 10)); })
        .map(function (userGroup) { return ({
        id: parseInt(userGroup.id, 10),
        label: userGroup.label,
    }); });
    switch (type) {
        case 'view':
            consistentPermissions.edit = ensureSubset(permissions['edit'], consistentPermissions.view);
            consistentPermissions.own = ensureSubset(permissions['own'], consistentPermissions.edit);
            break;
        case 'edit':
            consistentPermissions.view = ensureSuperset(permissions['view'], consistentPermissions.edit);
            consistentPermissions.own = ensureSubset(permissions['own'], consistentPermissions.edit);
            break;
        case 'own':
            consistentPermissions.edit = ensureSuperset(permissions['edit'], consistentPermissions.own);
            consistentPermissions.view = ensureSuperset(permissions['view'], consistentPermissions.edit);
            break;
    }
    return consistentPermissions;
}
exports.alterPermissionsConsistently = alterPermissionsConsistently;
//# sourceMappingURL=permissionsHelper.js.map