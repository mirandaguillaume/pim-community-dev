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
exports.AttributeOptionRichTextCheckbox = void 0;
var jsx_runtime_1 = require("react/jsx-runtime");
var shared_1 = require("@akeneo-pim-community/shared");
var akeneo_design_system_1 = require("akeneo-design-system");
var react_1 = require("react");
var react_query_1 = require("react-query");
var useSaveStatus_1 = require("../../../hooks/useSaveStatus");
var useUpdateTemplateAttribute_1 = require("../../../hooks/useUpdateTemplateAttribute");
var SaveStatusProvider_1 = require("../../providers/SaveStatusProvider");
var AttributeOptionRichTextCheckbox = function (_a) {
    var attribute = _a.attribute;
    var translate = (0, shared_1.useTranslate)();
    var queryClient = (0, react_query_1.useQueryClient)();
    var handleStatusListChange = (0, useSaveStatus_1.useSaveStatus)().handleStatusListChange;
    var saveStatusId = attribute.uuid + '_option_richtext';
    var mutation = (0, useUpdateTemplateAttribute_1.useUpdateTemplateAttribute)(attribute.template_uuid, attribute.uuid);
    var _b = __read((0, react_1.useState)(false), 2), isSaving = _b[0], setIsSaving = _b[1];
    var handleRichTextAreaChange = function () { return __awaiter(void 0, void 0, void 0, function () {
        return __generator(this, function (_a) {
            switch (_a.label) {
                case 0:
                    setIsSaving(true);
                    handleStatusListChange(saveStatusId, SaveStatusProvider_1.Status.SAVING);
                    return [4, mutation.mutateAsync({ isRichTextArea: !(attribute.type === 'richtext') })];
                case 1:
                    _a.sent();
                    return [4, queryClient.invalidateQueries(['get-template', attribute.template_uuid])];
                case 2:
                    _a.sent();
                    handleStatusListChange(saveStatusId, SaveStatusProvider_1.Status.SAVED);
                    setIsSaving(false);
                    return [2];
            }
        });
    }); };
    if (false === ['textarea', 'richtext'].includes(attribute.type)) {
        return null;
    }
    return ((0, jsx_runtime_1.jsx)(akeneo_design_system_1.Checkbox, __assign({ checked: attribute.type === 'richtext', onChange: handleRichTextAreaChange, readOnly: isSaving }, { children: translate('akeneo.category.template.attribute.settings.options.rich_text') }), void 0));
};
exports.AttributeOptionRichTextCheckbox = AttributeOptionRichTextCheckbox;
//# sourceMappingURL=AttributeOptionRichTextCheckbox.js.map