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
Object.defineProperty(exports, "__esModule", { value: true });
exports.TemplateLabelTranslationInput = void 0;
var jsx_runtime_1 = require("react/jsx-runtime");
var akeneo_design_system_1 = require("akeneo-design-system");
var react_query_1 = require("react-query");
var useSaveStatus_1 = require("../../../hooks/useSaveStatus");
var useUpdateTemplateProperties_1 = require("../../../hooks/useUpdateTemplateProperties");
var apiFetch_1 = require("../../../tools/apiFetch");
var useDebounceCallback_1 = require("../../../tools/useDebounceCallback");
var SaveStatusProvider_1 = require("../../providers/SaveStatusProvider");
var TemplateFormProvider_1 = require("../../providers/TemplateFormProvider");
var useFormData = function (template, localeCode) {
    var _a;
    var _b = __read((0, TemplateFormProvider_1.useTemplateForm)(), 1), state = _b[0];
    var formData = (_a = state.properties.labels) === null || _a === void 0 ? void 0 : _a[localeCode];
    if (undefined === formData) {
        return {
            value: template.labels[localeCode] || '',
            errors: [],
        };
    }
    return formData;
};
var TemplateLabelTranslationInput = function (_a) {
    var template = _a.template, locale = _a.locale;
    var queryClient = (0, react_query_1.useQueryClient)();
    var handleStatusListChange = (0, useSaveStatus_1.useSaveStatus)().handleStatusListChange;
    var saveStatusId = template.uuid + '_label_translation_' + locale.code;
    var _b = __read((0, TemplateFormProvider_1.useTemplateForm)(), 2), dispatch = _b[1];
    var mutation = (0, useUpdateTemplateProperties_1.useUpdateTemplateProperties)(template.uuid);
    var debouncedUpdateTemplateLabel = (0, useDebounceCallback_1.useDebounceCallback)(function (value) { return __awaiter(void 0, void 0, void 0, function () {
        var error_1;
        var _a;
        return __generator(this, function (_b) {
            switch (_b.label) {
                case 0:
                    _b.trys.push([0, 3, , 4]);
                    handleStatusListChange(saveStatusId, SaveStatusProvider_1.Status.SAVING);
                    return [4, mutation.mutateAsync({ labels: (_a = {}, _a[locale.code] = value, _a) })];
                case 1:
                    _b.sent();
                    return [4, queryClient.invalidateQueries(['get-template', template.uuid])];
                case 2:
                    _b.sent();
                    dispatch({
                        type: 'template_label_translation_saved',
                        payload: { localeCode: locale.code, value: value },
                    });
                    handleStatusListChange(saveStatusId, SaveStatusProvider_1.Status.SAVED);
                    return [3, 4];
                case 3:
                    error_1 = _b.sent();
                    if (error_1 instanceof apiFetch_1.BadRequestError) {
                        dispatch({
                            type: 'save_template_label_translation_failed',
                            payload: { localeCode: locale.code, errors: error_1.data.labels[locale.code] },
                        });
                        handleStatusListChange(saveStatusId, SaveStatusProvider_1.Status.ERRORS);
                    }
                    else {
                        handleStatusListChange(saveStatusId, SaveStatusProvider_1.Status.SAVED);
                        throw error_1;
                    }
                    return [3, 4];
                case 4: return [2];
            }
        });
    }); }, 300);
    var handleChange = function (value) { return __awaiter(void 0, void 0, void 0, function () {
        return __generator(this, function (_a) {
            dispatch({
                type: 'template_label_translation_changed',
                payload: { localeCode: locale.code, value: value },
            });
            debouncedUpdateTemplateLabel(value);
            handleStatusListChange(saveStatusId, SaveStatusProvider_1.Status.EDITING);
            return [2];
        });
    }); };
    var formData = useFormData(template, locale.code);
    return ((0, jsx_runtime_1.jsxs)(akeneo_design_system_1.Field, __assign({ locale: locale.code, label: locale.label }, { children: [(0, jsx_runtime_1.jsx)(akeneo_design_system_1.TextInput, { value: formData.value, onChange: handleChange }, void 0), formData.errors.map(function (message) { return ((0, jsx_runtime_1.jsx)(akeneo_design_system_1.Helper, __assign({ level: "error" }, { children: message }), message)); })] }), locale.code));
};
exports.TemplateLabelTranslationInput = TemplateLabelTranslationInput;
//# sourceMappingURL=TemplateLabelTranslationInput.js.map