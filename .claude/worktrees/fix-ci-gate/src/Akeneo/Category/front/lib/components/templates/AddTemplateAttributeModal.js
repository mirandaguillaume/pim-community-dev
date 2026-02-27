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
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.AddTemplateAttributeModal = void 0;
var jsx_runtime_1 = require("react/jsx-runtime");
var shared_1 = require("@akeneo-pim-community/shared");
var react_1 = require("react");
var react_query_1 = require("react-query");
var useCreateAttribute_1 = require("../../hooks/useCreateAttribute");
var user_context_1 = require("@akeneo-pim-community/shared/lib/dependencies/user-context");
var akeneo_design_system_1 = require("akeneo-design-system");
var styled_components_1 = __importDefault(require("styled-components"));
var Content = styled_components_1.default.div(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  padding-bottom: 20px;\n"], ["\n  padding-bottom: 20px;\n"])));
var FieldSet = styled_components_1.default.div(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n  & > * {\n    margin-top: 20px;\n  }\n"], ["\n  & > * {\n    margin-top: 20px;\n  }\n"])));
var HelperContent = (0, styled_components_1.default)(akeneo_design_system_1.Helper)(templateObject_3 || (templateObject_3 = __makeTemplateObject(["\n  min-width: 200px;\n  max-width: 460px;\n"], ["\n  min-width: 200px;\n  max-width: 460px;\n"])));
var AddTemplateAttributeModal = function (_a) {
    var templateId = _a.templateId, onClose = _a.onClose, onSuccess = _a.onSuccess;
    var defaultCatalogLocale = user_context_1.userContext.get('catalog_default_locale');
    var mutation = (0, useCreateAttribute_1.useCreateAttribute)();
    var notify = (0, shared_1.useNotify)();
    var queryClient = (0, react_query_1.useQueryClient)();
    var _b = __read((0, react_1.useState)({ label: '', code: '', type: 'text', isScopable: false, isLocalizable: false }), 2), form = _b[0], setForm = _b[1];
    var _c = __read((0, react_1.useState)({}), 2), error = _c[0], setError = _c[1];
    var attributeTypes = [
        {
            type: 'text',
            label: (0, shared_1.translate)('akeneo.category.template.add_attribute.confirmation_modal.input.type.option_title.text'),
        },
        {
            type: 'textarea',
            label: (0, shared_1.translate)('akeneo.category.template.add_attribute.confirmation_modal.input.type.option_title.textarea'),
        },
        {
            type: 'image',
            label: (0, shared_1.translate)('akeneo.category.template.add_attribute.confirmation_modal.input.type.option_title.image'),
        },
    ];
    var displayError = function (errorMessages) {
        return errorMessages.map(function (message) {
            return ((0, jsx_runtime_1.jsx)(akeneo_design_system_1.Helper, __assign({ level: "error" }, { children: message }), message));
        });
    };
    var handleCreate = function () {
        mutation.mutate({
            templateId: templateId,
            code: form.code,
            locale: defaultCatalogLocale,
            label: form.label,
            type: form.type,
            isScopable: form.isScopable,
            isLocalizable: form.isLocalizable,
        }, {
            onError: function (error) {
                setError(error.data);
            },
            onSuccess: function () { return __awaiter(void 0, void 0, void 0, function () {
                return __generator(this, function (_a) {
                    switch (_a.label) {
                        case 0: return [4, queryClient.invalidateQueries(['get-template', templateId])];
                        case 1:
                            _a.sent();
                            notify(shared_1.NotificationLevel.SUCCESS, (0, shared_1.translate)('akeneo.category.template.add_attribute.success.notification'));
                            onSuccess && onSuccess();
                            onClose();
                            return [2];
                    }
                });
            }); },
        });
    };
    return ((0, jsx_runtime_1.jsxs)(akeneo_design_system_1.Modal, __assign({ illustration: (0, jsx_runtime_1.jsx)(akeneo_design_system_1.ProductCategoryIllustration, {}, void 0), onClose: onClose, closeTitle: (0, shared_1.translate)('pim_common.close') }, { children: [(0, jsx_runtime_1.jsx)(akeneo_design_system_1.Modal.SectionTitle, __assign({ color: "brand" }, { children: (0, shared_1.translate)('akeneo.category.template.add_attribute.confirmation_modal.section_title') }), void 0), (0, jsx_runtime_1.jsx)(akeneo_design_system_1.Modal.Title, { children: (0, shared_1.translate)('akeneo.category.template.add_attribute.confirmation_modal.title') }, void 0), (0, jsx_runtime_1.jsx)(Content, { children: (0, jsx_runtime_1.jsxs)(FieldSet, { children: [(0, jsx_runtime_1.jsxs)(HelperContent, __assign({ level: "info" }, { children: [(0, shared_1.translate)('akeneo.category.template.add_attribute.confirmation_modal.head_helper'), ' ', (0, jsx_runtime_1.jsx)(akeneo_design_system_1.Link, __assign({ href: "https://help.akeneo.com/serenity-take-the-power-over-your-products/serenity-enrich-your-category" }, { children: (0, shared_1.translate)('akeneo.category.template.add_attribute.confirmation_modal.link') }), void 0)] }), void 0), (0, jsx_runtime_1.jsxs)(akeneo_design_system_1.Field, __assign({ label: (0, shared_1.translate)('pim_common.label'), locale: defaultCatalogLocale }, { children: [(0, jsx_runtime_1.jsx)(akeneo_design_system_1.TextInput, { value: form.label, invalid: !!error.label, onChange: function (label) {
                                        setForm(__assign(__assign({}, form), { label: label }));
                                    } }, void 0), error.label && displayError(error.label)] }), void 0), (0, jsx_runtime_1.jsxs)(akeneo_design_system_1.Field, __assign({ label: (0, shared_1.translate)('pim_common.code'), requiredLabel: (0, shared_1.translate)('pim_common.required_label') }, { children: [(0, jsx_runtime_1.jsx)(akeneo_design_system_1.TextInput, { value: form.code, invalid: !!error.code, onChange: function (code) {
                                        setForm(__assign(__assign({}, form), { code: code }));
                                    } }, void 0), error.code && displayError(error.code)] }), void 0), (0, jsx_runtime_1.jsx)(akeneo_design_system_1.Field, __assign({ label: (0, shared_1.translate)('akeneo.category.template.add_attribute.confirmation_modal.input.type.label') }, { children: (0, jsx_runtime_1.jsx)(akeneo_design_system_1.SelectInput, __assign({ emptyResultLabel: (0, shared_1.translate)('akeneo.category.template.add_attribute.confirmation_modal.input.type.empty'), openLabel: '', clearable: false, value: form.type, onChange: function (type) {
                                    setForm(__assign(__assign({}, form), { type: type }));
                                } }, { children: attributeTypes.map(function (attribute) {
                                    return ((0, jsx_runtime_1.jsx)(akeneo_design_system_1.SelectInput.Option, __assign({ title: attribute.label, value: attribute.type }, { children: attribute.label }), attribute.type));
                                }) }), void 0) }), void 0), (0, jsx_runtime_1.jsx)(akeneo_design_system_1.Checkbox, __assign({ checked: form.isScopable, onChange: function (value) {
                                setForm(__assign(__assign({}, form), { isScopable: value }));
                            } }, { children: (0, shared_1.translate)('pim_enrich.entity.attribute.property.scopable') }), void 0), (0, jsx_runtime_1.jsx)(akeneo_design_system_1.Checkbox, __assign({ checked: form.isLocalizable, onChange: function (value) {
                                setForm(__assign(__assign({}, form), { isLocalizable: value }));
                            } }, { children: (0, shared_1.translate)('pim_enrich.entity.attribute.property.localizable') }), void 0), (0, jsx_runtime_1.jsx)(HelperContent, __assign({ level: "info", inline: true }, { children: (0, shared_1.translate)('akeneo.category.template.add_attribute.confirmation_modal.tail_helper') }), void 0)] }, void 0) }, void 0), (0, jsx_runtime_1.jsxs)(akeneo_design_system_1.Modal.BottomButtons, { children: [(0, jsx_runtime_1.jsx)(akeneo_design_system_1.Button, __assign({ level: "tertiary", onClick: onClose }, { children: (0, shared_1.translate)('pim_common.cancel') }), void 0), (0, jsx_runtime_1.jsx)(akeneo_design_system_1.Button, __assign({ disabled: mutation.isLoading, level: "primary", onClick: handleCreate }, { children: (0, shared_1.translate)('akeneo.category.template.add_attribute.confirmation_modal.create') }), void 0)] }, void 0)] }), void 0));
};
exports.AddTemplateAttributeModal = AddTemplateAttributeModal;
var templateObject_1, templateObject_2, templateObject_3;
//# sourceMappingURL=AddTemplateAttributeModal.js.map