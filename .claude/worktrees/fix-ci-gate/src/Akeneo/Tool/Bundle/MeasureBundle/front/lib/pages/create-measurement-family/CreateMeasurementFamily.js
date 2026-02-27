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
Object.defineProperty(exports, "__esModule", { value: true });
exports.CreateMeasurementFamily = void 0;
var react_1 = __importStar(require("react"));
var akeneo_design_system_1 = require("akeneo-design-system");
var use_create_measurement_family_saver_1 = require("./hooks/use-create-measurement-family-saver");
var create_measurement_family_form_1 = require("./form/create-measurement-family-form");
var use_form_1 = require("../../hooks/use-form");
var shared_1 = require("@akeneo-pim-community/shared");
var CreateMeasurementFamily = function (_a) {
    var isOpen = _a.isOpen, onClose = _a.onClose;
    var translate = (0, shared_1.useTranslate)();
    var notify = (0, shared_1.useNotify)();
    var locale = (0, shared_1.useUserContext)().get('uiLocale');
    var _b = (0, use_form_1.useForm)((0, create_measurement_family_form_1.initializeCreateMeasurementFamilyForm)()), form = _b[0], setFormValue = _b[1];
    var saveMeasurementFamily = (0, use_create_measurement_family_saver_1.useCreateMeasurementFamilySaver)();
    var _c = (0, react_1.useState)([]), errors = _c[0], setErrors = _c[1];
    var handleClose = (0, react_1.useCallback)(onClose, [onClose]);
    var handleSave = (0, react_1.useCallback)(function () { return __awaiter(void 0, void 0, void 0, function () {
        var measurementFamily, response, error_1;
        return __generator(this, function (_a) {
            switch (_a.label) {
                case 0:
                    _a.trys.push([0, 2, , 3]);
                    measurementFamily = (0, create_measurement_family_form_1.createMeasurementFamilyFromForm)(form, locale);
                    return [4, saveMeasurementFamily(measurementFamily)];
                case 1:
                    response = _a.sent();
                    switch (response.success) {
                        case true:
                            notify(shared_1.NotificationLevel.SUCCESS, translate('measurements.create_family.flash.success'));
                            handleClose(measurementFamily.code);
                            break;
                        case false:
                            setErrors(response.errors);
                            break;
                    }
                    return [3, 3];
                case 2:
                    error_1 = _a.sent();
                    console.error(error_1);
                    notify(shared_1.NotificationLevel.ERROR, translate('measurements.create_family.flash.error'));
                    return [3, 3];
                case 3: return [2];
            }
        });
    }); }, [form, locale, saveMeasurementFamily, notify, translate, handleClose, setErrors]);
    if (!isOpen)
        return null;
    return (react_1.default.createElement(akeneo_design_system_1.Modal, { closeTitle: translate('pim_common.close'), onClose: function () { return handleClose(); }, illustration: react_1.default.createElement(akeneo_design_system_1.MeasurementIllustration, null) },
        react_1.default.createElement(akeneo_design_system_1.Modal.SectionTitle, { color: "brand" }, translate('measurements.title.measurement')),
        react_1.default.createElement(akeneo_design_system_1.Modal.Title, null, translate('measurements.family.add_new_measurement_family')),
        react_1.default.createElement(shared_1.Section, null,
            react_1.default.createElement(akeneo_design_system_1.SectionTitle, null,
                react_1.default.createElement(akeneo_design_system_1.SectionTitle.Title, null, translate('pim_common.properties'))),
            react_1.default.createElement(shared_1.TextField, { label: translate('pim_common.code'), value: form.family_code, onChange: function (value) { return setFormValue('family_code', value); }, required: true, errors: (0, shared_1.getErrorsForPath)(errors, 'code') }),
            react_1.default.createElement(shared_1.TextField, { label: translate('pim_common.label'), value: form.family_label, onChange: function (value) { return setFormValue('family_label', value); }, locale: locale, errors: (0, shared_1.getErrorsForPath)(errors, "labels[" + locale + "]") }),
            react_1.default.createElement("div", null,
                react_1.default.createElement(akeneo_design_system_1.SectionTitle, null,
                    react_1.default.createElement(akeneo_design_system_1.SectionTitle.Title, null, translate('measurements.family.standard_unit'))),
                react_1.default.createElement(akeneo_design_system_1.Helper, { level: "warning" }, translate('measurements.family.standard_unit_is_not_editable_after_creation'))),
            react_1.default.createElement(shared_1.TextField, { label: translate('pim_common.code'), value: form.standard_unit_code, onChange: function (value) { return setFormValue('standard_unit_code', value); }, required: true, errors: (0, shared_1.getErrorsForPath)(errors, 'units[0][code]') }),
            react_1.default.createElement(shared_1.TextField, { label: translate('pim_common.label'), value: form.standard_unit_label, onChange: function (value) { return setFormValue('standard_unit_label', value); }, locale: locale, errors: (0, shared_1.getErrorsForPath)(errors, "units[0][labels][" + locale + "]") }),
            react_1.default.createElement(shared_1.TextField, { label: translate('measurements.form.input.symbol'), value: form.standard_unit_symbol, onChange: function (value) { return setFormValue('standard_unit_symbol', value); }, errors: (0, shared_1.getErrorsForPath)(errors, 'units[0][symbol]') })),
        react_1.default.createElement(akeneo_design_system_1.Modal.BottomButtons, null,
            react_1.default.createElement(akeneo_design_system_1.Button, { onClick: handleSave }, translate('pim_common.save')))));
};
exports.CreateMeasurementFamily = CreateMeasurementFamily;
//# sourceMappingURL=CreateMeasurementFamily.js.map