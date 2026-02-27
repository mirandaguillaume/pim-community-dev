"use strict";
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
Object.defineProperty(exports, "__esModule", { value: true });
exports.useEditCategoryForm = void 0;
var react_1 = require("react");
var fp_1 = require("lodash/fp");
var shared_1 = require("@akeneo-pim-community/shared");
var infrastructure_1 = require("../infrastructure");
var useCategory_1 = require("./useCategory");
var components_1 = require("../components");
var models_1 = require("../models");
var helpers_1 = require("../helpers");
var useTemplateByTemplateUuid_1 = require("./useTemplateByTemplateUuid");
var ResponseStatus_1 = require("../models/ResponseStatus");
var react_router_1 = require("react-router");
var react_query_1 = require("react-query");
var useEditCategoryForm = function (categoryId) {
    var _a;
    var router = (0, shared_1.useRouter)();
    var notify = (0, shared_1.useNotify)();
    var translate = (0, shared_1.useTranslate)();
    var history = (0, react_router_1.useHistory)();
    var queryClient = (0, react_query_1.useQueryClient)();
    var _b = (0, useCategory_1.useCategory)(categoryId), loadCategory = _b.load, fetchedCategory = _b.category, categoryStatus = _b.status;
    var template = (0, useTemplateByTemplateUuid_1.useTemplateByTemplateUuid)((_a = fetchedCategory === null || fetchedCategory === void 0 ? void 0 : fetchedCategory.template_uuid) !== null && _a !== void 0 ? _a : null).data;
    var _c = __read((0, react_1.useState)(null), 2), category = _c[0], setCategory = _c[1];
    var _d = __read((0, react_1.useState)(null), 2), categoryEdited = _d[0], setCategoryEdited = _d[1];
    var _e = __read((0, react_1.useState)(true), 2), applyPermissionsOnChildren = _e[0], setApplyPermissionsOnChildren = _e[1];
    var _f = __read((0, react_1.useState)(0), 2), historyVersion = _f[0], setHistoryVersion = _f[1];
    var _g = (0, react_1.useContext)(components_1.EditCategoryContext), channels = _g.channels, locales = _g.locales;
    var setCanLeavePage = (0, react_1.useContext)(components_1.CanLeavePageContext).setCanLeavePage;
    var isModified = categoryStatus === 'fetched' && !!category && !!categoryEdited && !(0, helpers_1.categoriesAreEqual)(category, categoryEdited);
    var initializeEditionState = function (category, template, channels, locales) {
        var populated = (0, helpers_1.populateCategory)(category, template, Object.keys(channels), Object.keys(locales));
        setCategory(populated);
        setCategoryEdited((0, fp_1.cloneDeep)(populated));
    };
    (0, react_1.useEffect)(function () {
        loadCategory();
    }, [categoryId]);
    (0, react_1.useEffect)(function () {
        if (fetchedCategory === null)
            return;
        initializeEditionState(fetchedCategory, template !== null && template !== void 0 ? template : null, channels, locales);
    }, [fetchedCategory, template, locales]);
    (0, react_1.useEffect)(function () {
        setCanLeavePage(!isModified);
    }, [setCanLeavePage, isModified]);
    (0, react_1.useEffect)(function () { return function () {
        setCanLeavePage(true);
    }; }, [setCanLeavePage]);
    var saveCategory = function () { return __awaiter(void 0, void 0, void 0, function () {
        var response;
        return __generator(this, function (_a) {
            switch (_a.label) {
                case 0:
                    if (categoryEdited === null) {
                        return [2];
                    }
                    return [4, (0, infrastructure_1.saveEditCategoryForm)(router, categoryEdited, {
                            applyPermissionsOnChildren: applyPermissionsOnChildren,
                            populateResponseCategory: function (category) {
                                return (0, helpers_1.populateCategory)(category, template, Object.keys(channels), Object.keys(locales));
                            },
                        })];
                case 1:
                    response = _a.sent();
                    if (!response.success) return [3, 3];
                    initializeEditionState(response.category, template !== null && template !== void 0 ? template : null, channels, locales);
                    setHistoryVersion(function (prevVersion) { return prevVersion + 1; });
                    notify(shared_1.NotificationLevel.SUCCESS, translate('pim_enrich.entity.category.content.edit.success'));
                    return [4, queryClient.invalidateQueries(['get-template', fetchedCategory === null || fetchedCategory === void 0 ? void 0 : fetchedCategory.template_uuid])];
                case 2:
                    _a.sent();
                    return [3, 4];
                case 3:
                    notify(shared_1.NotificationLevel.ERROR, response.error.message);
                    if (response.error.code && response.error.code === ResponseStatus_1.DEACTIVATED_TEMPLATE) {
                        history.push('/');
                    }
                    _a.label = 4;
                case 4: return [2];
            }
        });
    }); };
    var onChangeCategoryLabel = function (localeCode, label) {
        if (categoryEdited === null) {
            return;
        }
        setCategoryEdited((0, fp_1.set)(['properties', 'labels', localeCode], label === '' ? null : label, categoryEdited));
    };
    var onChangePermissions = function (userGroups, type, values) {
        if (categoryEdited === null) {
            return;
        }
        var consistentPermissions = (0, helpers_1.alterPermissionsConsistently)(userGroups, categoryEdited.permissions, { type: type, values: values });
        setCategoryEdited((0, fp_1.set)('permissions', consistentPermissions, categoryEdited));
    };
    var onChangeAttribute = function (attribute, channelCode, localeCode, attributeValue) {
        if (categoryEdited === null) {
            return;
        }
        var compositeKey = (0, models_1.buildCompositeKey)(attribute, channelCode, localeCode);
        var compositeKeyWithoutChannelAndLocale = (0, models_1.buildCompositeKey)(attribute);
        var value = {
            data: attributeValue,
            channel: attribute.is_scopable ? channelCode : null,
            locale: attribute.is_localizable ? localeCode : null,
            attribute_code: compositeKeyWithoutChannelAndLocale,
        };
        var newCategoryEdited = (0, fp_1.set)(['attributes', compositeKey], value, categoryEdited);
        if ((0, helpers_1.categoriesAreEqual)(categoryEdited, newCategoryEdited)) {
            return;
        }
        setCategoryEdited(newCategoryEdited);
    };
    return {
        categoryFetchingStatus: categoryStatus,
        category: categoryEdited,
        template: template,
        applyPermissionsOnChildren: applyPermissionsOnChildren,
        onChangeCategoryLabel: onChangeCategoryLabel,
        onChangePermissions: onChangePermissions,
        onChangeAttribute: onChangeAttribute,
        onChangeApplyPermissionsOnChildren: setApplyPermissionsOnChildren,
        isModified: isModified,
        saveCategory: saveCategory,
        historyVersion: historyVersion,
    };
};
exports.useEditCategoryForm = useEditCategoryForm;
//# sourceMappingURL=useEditCategoryForm.js.map