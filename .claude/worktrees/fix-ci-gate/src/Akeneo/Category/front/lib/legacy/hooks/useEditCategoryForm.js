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
var __awaiter = (this && this.__awaiter) || function (thisArg, _arguments, P, generator) {
    function adopt(value) { return value instanceof P ? value : new P(function (resolve) { resolve(value); }); }
    return new (P || (P = Promise))(function (resolve, reject) {
        function fulfilled(value) { try { step(generator.next(value)); } catch (e) { reject(e); } }
        function rejected(value) { try { step(generator["throw"](value)); } catch (e) { reject(e); } }
        function step(result) { result.done ? resolve(result.value) : adopt(result.value).then(fulfilled, rejected); }
        step((generator = generator.apply(thisArg, _arguments || [])).next());
    });
};
var __generator = (this && this.__generator) || function (thisArg, body) {
    var _ = { label: 0, sent: function() { if (t[0] & 1) throw t[1]; return t[1]; }, trys: [], ops: [] }, f, y, t, g;
    return g = { next: verb(0), "throw": verb(1), "return": verb(2) }, typeof Symbol === "function" && (g[Symbol.iterator] = function() { return this; }), g;
    function verb(n) { return function (v) { return step([n, v]); }; }
    function step(op) {
        if (f) throw new TypeError("Generator is already executing.");
        while (_) try {
            if (f = 1, y && (t = op[0] & 2 ? y["return"] : op[0] ? y["throw"] || ((t = y["return"]) && t.call(y), 0) : y.next) && !(t = t.call(y, op[1])).done) return t;
            if (y = 0, t) op = [op[0] & 2, t.value];
            switch (op[0]) {
                case 0: case 1: t = op; break;
                case 4: _.label++; return { value: op[1], done: false };
                case 5: _.label++; y = op[1]; op = [0]; continue;
                case 7: op = _.ops.pop(); _.trys.pop(); continue;
                default:
                    if (!(t = _.trys, t = t.length > 0 && t[t.length - 1]) && (op[0] === 6 || op[0] === 2)) { _ = 0; continue; }
                    if (op[0] === 3 && (!t || (op[1] > t[0] && op[1] < t[3]))) { _.label = op[1]; break; }
                    if (op[0] === 6 && _.label < t[1]) { _.label = t[1]; t = op; break; }
                    if (t && _.label < t[2]) { _.label = t[2]; _.ops.push(op); break; }
                    if (t[2]) _.ops.pop();
                    _.trys.pop(); continue;
            }
            op = body.call(thisArg, _);
        } catch (e) { op = [6, e]; y = 0; } finally { f = t = 0; }
        if (op[0] & 5) throw op[1]; return { value: op[0] ? op[1] : void 0, done: true };
    }
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
var __values = (this && this.__values) || function(o) {
    var s = typeof Symbol === "function" && Symbol.iterator, m = s && o[s], i = 0;
    if (m) return m.call(o);
    if (o && typeof o.length === "number") return {
        next: function () {
            if (o && i >= o.length) o = void 0;
            return { value: o && o[i++], done: !o };
        }
    };
    throw new TypeError(s ? "Object is not iterable." : "Symbol.iterator is not defined.");
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.useEditCategoryForm = void 0;
var react_1 = require("react");
var infrastructure_1 = require("../infrastructure");
var shared_1 = require("@akeneo-pim-community/shared");
var useCategory_1 = require("./useCategory");
var components_1 = require("../components");
var helpers_1 = require("../helpers");
var useEditCategoryForm = function (categoryId) {
    var router = (0, shared_1.useRouter)();
    var notify = (0, shared_1.useNotify)();
    var translate = (0, shared_1.useTranslate)();
    var _a = __read((0, useCategory_1.useCategory)(categoryId), 3), categoryData = _a[0], loadCategory = _a[1], categoryLoadingStatus = _a[2];
    var _b = __read((0, react_1.useState)(null), 2), category = _b[0], setCategory = _b[1];
    var _c = __read((0, react_1.useState)(null), 2), originalFormData = _c[0], setOriginalFormData = _c[1];
    var _d = __read((0, react_1.useState)(null), 2), editedFormData = _d[0], setEditedFormData = _d[1];
    var _e = __read((0, react_1.useState)(false), 2), thereAreUnsavedChanges = _e[0], setThereAreUnsavedChanges = _e[1];
    var _f = __read((0, react_1.useState)(0), 2), historyVersion = _f[0], setHistoryVersion = _f[1];
    var setCanLeavePage = (0, react_1.useContext)(components_1.EditCategoryContext).setCanLeavePage;
    var haveLabelsBeenChanged = (0, react_1.useCallback)(function () {
        var e_1, _a;
        if (originalFormData === null || editedFormData === null) {
            return false;
        }
        try {
            for (var _b = __values(Object.entries(editedFormData.label)), _c = _b.next(); !_c.done; _c = _b.next()) {
                var _d = __read(_c.value, 2), locale = _d[0], changedLabel = _d[1];
                if (!originalFormData.label.hasOwnProperty(locale) && changedLabel.value === '') {
                    return false;
                }
                if (!originalFormData.label.hasOwnProperty(locale) ||
                    originalFormData.label[locale].value !== changedLabel.value) {
                    return true;
                }
            }
        }
        catch (e_1_1) { e_1 = { error: e_1_1 }; }
        finally {
            try {
                if (_c && !_c.done && (_a = _b.return)) _a.call(_b);
            }
            finally { if (e_1) throw e_1.error; }
        }
        return false;
    }, [originalFormData, editedFormData]);
    var havePermissionsBeenChanged = (0, react_1.useCallback)(function () {
        if (originalFormData === null ||
            editedFormData === null ||
            !originalFormData.permissions ||
            !editedFormData.permissions) {
            return false;
        }
        return (JSON.stringify(originalFormData.permissions.view.value) !==
            JSON.stringify(editedFormData.permissions.view.value) ||
            JSON.stringify(originalFormData.permissions.edit.value) !==
                JSON.stringify(editedFormData.permissions.edit.value) ||
            JSON.stringify(originalFormData.permissions.own.value) !== JSON.stringify(editedFormData.permissions.own.value));
    }, [originalFormData, editedFormData]);
    var onChangeCategoryLabel = function (locale, label) {
        var _a;
        if (editedFormData === null || !editedFormData.label.hasOwnProperty(locale)) {
            return;
        }
        var editedLabel = __assign(__assign({}, editedFormData.label[locale]), { value: label });
        setEditedFormData(__assign(__assign({}, editedFormData), { label: __assign(__assign({}, editedFormData.label), (_a = {}, _a[locale] = editedLabel, _a)) }));
    };
    var onChangePermissions = function (type, values) {
        if (editedFormData === null || !editedFormData.permissions) {
            return;
        }
        switch (type) {
            case 'view':
                var newViewPermissions = (0, helpers_1.computeNewViewPermissions)(editedFormData, values);
                setEditedFormData(newViewPermissions);
                break;
            case 'edit':
                var newEditPermissions = (0, helpers_1.computeNewEditPermissions)(editedFormData, values);
                setEditedFormData(newEditPermissions);
                break;
            case 'own':
                var newOwnPermissions = (0, helpers_1.computeNewOwnPermissions)(editedFormData, values);
                setEditedFormData(newOwnPermissions);
                break;
        }
    };
    var onChangeApplyPermissionsOnChildren = function (value) {
        if (editedFormData === null || !editedFormData.permissions) {
            return;
        }
        var editedApplyOnChildren = __assign(__assign({}, editedFormData.permissions.apply_on_children), { value: value === true ? '1' : '0' });
        setEditedFormData(__assign(__assign({}, editedFormData), { permissions: __assign(__assign({}, editedFormData.permissions), { apply_on_children: editedApplyOnChildren }) }));
    };
    var saveCategory = (0, react_1.useCallback)(function () { return __awaiter(void 0, void 0, void 0, function () {
        var response, refreshedToken;
        return __generator(this, function (_a) {
            switch (_a.label) {
                case 0:
                    if (categoryData === null || editedFormData === null) {
                        return [2];
                    }
                    return [4, (0, infrastructure_1.saveEditCategoryForm)(router, categoryData.category.id, editedFormData)];
                case 1:
                    response = _a.sent();
                    if (response.success) {
                        setHistoryVersion(function (prevVersion) { return prevVersion + 1; });
                        notify(shared_1.NotificationLevel.SUCCESS, translate('pim_enrich.entity.category.content.edit.success'));
                        setOriginalFormData(response.form);
                        setCategory(response.category);
                    }
                    else {
                        notify(shared_1.NotificationLevel.ERROR, translate('pim_enrich.entity.category.content.edit.fail'));
                        refreshedToken = __assign(__assign({}, editedFormData._token), { value: response.form._token.value });
                        setEditedFormData(__assign(__assign({}, editedFormData), { _token: refreshedToken }));
                    }
                    return [2];
            }
        });
    }); }, [categoryData, editedFormData]);
    (0, react_1.useEffect)(function () {
        loadCategory();
    }, [categoryId]);
    (0, react_1.useEffect)(function () {
        if (categoryData === null) {
            return;
        }
        setOriginalFormData(categoryData.form);
        setCategory(categoryData.category);
    }, [categoryData]);
    (0, react_1.useEffect)(function () {
        if (originalFormData === null) {
            return;
        }
        if (originalFormData.permissions && editedFormData !== null && editedFormData.permissions) {
            setEditedFormData(__assign(__assign({}, originalFormData), { permissions: __assign(__assign({}, originalFormData.permissions), { apply_on_children: editedFormData.permissions.apply_on_children }) }));
        }
        else {
            setEditedFormData(__assign({}, originalFormData));
        }
    }, [originalFormData]);
    (0, react_1.useEffect)(function () {
        if (editedFormData !== null) {
            setThereAreUnsavedChanges(haveLabelsBeenChanged() || havePermissionsBeenChanged());
        }
    }, [editedFormData]);
    (0, react_1.useEffect)(function () {
        setCanLeavePage(!thereAreUnsavedChanges);
    }, [thereAreUnsavedChanges]);
    return {
        categoryLoadingStatus: categoryLoadingStatus,
        category: category,
        formData: editedFormData,
        onChangeCategoryLabel: onChangeCategoryLabel,
        onChangePermissions: onChangePermissions,
        onChangeApplyPermissionsOnChildren: onChangeApplyPermissionsOnChildren,
        thereAreUnsavedChanges: thereAreUnsavedChanges,
        saveCategory: saveCategory,
        historyVersion: historyVersion,
    };
};
exports.useEditCategoryForm = useEditCategoryForm;
//# sourceMappingURL=useEditCategoryForm.js.map