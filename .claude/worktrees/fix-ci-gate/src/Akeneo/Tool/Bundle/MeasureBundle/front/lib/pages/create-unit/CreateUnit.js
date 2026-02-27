"use strict";
var __makeTemplateObject = (this && this.__makeTemplateObject) || function (cooked, raw) {
    if (Object.defineProperty) { Object.defineProperty(cooked, "raw", { value: raw }); } else { cooked.raw = raw; }
    return cooked;
};
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
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.CreateUnit = void 0;
var react_1 = __importStar(require("react"));
var styled_components_1 = __importDefault(require("styled-components"));
var akeneo_design_system_1 = require("akeneo-design-system");
var use_form_1 = require("../../hooks/use-form");
var measurement_family_1 = require("../../model/measurement-family");
var create_unit_form_1 = require("../create-unit/form/create-unit-form");
var use_create_unit_validator_1 = require("../create-unit/hooks/use-create-unit-validator");
var OperationCollection_1 = require("../common/OperationCollection");
var shared_1 = require("@akeneo-pim-community/shared");
var config_context_1 = require("../../context/config-context");
var FormGroup = (0, styled_components_1.default)(shared_1.Section)(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  max-width: 400px;\n"], ["\n  max-width: 400px;\n"])));
var CreateUnit = function (_a) {
    var onClose = _a.onClose, onNewUnit = _a.onNewUnit, measurementFamily = _a.measurementFamily;
    var translate = (0, shared_1.useTranslate)();
    var notify = (0, shared_1.useNotify)();
    var locale = (0, shared_1.useUserContext)().get('uiLocale');
    var config = (0, react_1.useContext)(config_context_1.ConfigContext);
    var _b = (0, use_form_1.useForm)((0, create_unit_form_1.initializeCreateUnitForm)()), form = _b[0], setFormValue = _b[1], clearForm = _b[2];
    var validate = (0, use_create_unit_validator_1.useCreateUnitValidator)();
    var _c = (0, react_1.useState)(false), createAnotherUnit = _c[0], setCreateAnotherUnit = _c[1];
    var handleClose = (0, react_1.useCallback)(function () {
        clearForm();
        onClose();
    }, [clearForm, onClose]);
    var _d = (0, react_1.useState)([]), errors = _d[0], setErrors = _d[1];
    var measurementFamilyLabel = (0, measurement_family_1.getMeasurementFamilyLabel)(measurementFamily, locale);
    var firstFieldRef = (0, react_1.useRef)(null);
    var focusFirstField = (0, akeneo_design_system_1.useAutoFocus)(firstFieldRef);
    var handleAdd = (0, react_1.useCallback)(function () { return __awaiter(void 0, void 0, void 0, function () {
        var formValidationErrors, unit, response, error_1;
        return __generator(this, function (_a) {
            switch (_a.label) {
                case 0:
                    _a.trys.push([0, 2, , 3]);
                    setErrors([]);
                    formValidationErrors = (0, create_unit_form_1.validateCreateUnitForm)(form, measurementFamily, translate);
                    if (0 < formValidationErrors.length) {
                        setErrors(formValidationErrors);
                        return [2];
                    }
                    unit = (0, create_unit_form_1.createUnitFromForm)(form, locale);
                    return [4, validate(measurementFamily.code, unit)];
                case 1:
                    response = _a.sent();
                    switch (response.valid) {
                        case true:
                            onNewUnit(unit);
                            focusFirstField();
                            createAnotherUnit ? clearForm() : handleClose();
                            break;
                        case false:
                            setErrors(response.errors);
                            break;
                    }
                    return [3, 3];
                case 2:
                    error_1 = _a.sent();
                    console.error(error_1);
                    notify(shared_1.NotificationLevel.ERROR, translate('measurements.add_unit.flash.error'));
                    return [3, 3];
                case 3: return [2];
            }
        });
    }); }, [
        form,
        locale,
        validate,
        measurementFamily,
        notify,
        onNewUnit,
        createAnotherUnit,
        clearForm,
        handleClose,
        setErrors,
        translate,
        focusFirstField,
    ]);
    (0, akeneo_design_system_1.useShortcut)(akeneo_design_system_1.Key.Enter, handleAdd);
    return (react_1.default.createElement(akeneo_design_system_1.Modal, { closeTitle: translate('pim_common.close'), onClose: handleClose, illustration: react_1.default.createElement(akeneo_design_system_1.MeasurementIllustration, null) },
        react_1.default.createElement(akeneo_design_system_1.Modal.SectionTitle, { color: "brand" },
            translate('measurements.title.measurement'),
            " / ",
            measurementFamilyLabel),
        react_1.default.createElement(akeneo_design_system_1.Modal.Title, null, translate('measurements.unit.add_new')),
        react_1.default.createElement(shared_1.Section, null,
            measurementFamily.is_locked && (react_1.default.createElement(akeneo_design_system_1.Helper, { level: "warning" }, translate('measurements.unit.will_be_read_only'))),
            react_1.default.createElement(FormGroup, null,
                react_1.default.createElement(shared_1.TextField, { ref: firstFieldRef, label: translate('pim_common.code'), value: form.code, onChange: function (value) { return setFormValue('code', value); }, required: true, errors: (0, shared_1.getErrorsForPath)(errors, 'code') }),
                react_1.default.createElement(shared_1.TextField, { label: translate('pim_common.label'), value: form.label, onChange: function (value) { return setFormValue('label', value); }, locale: locale, errors: (0, shared_1.getErrorsForPath)(errors, "labels[" + locale + "]") }),
                react_1.default.createElement(shared_1.TextField, { label: translate('measurements.form.input.symbol'), value: form.symbol, onChange: function (value) { return setFormValue('symbol', value); }, errors: (0, shared_1.getErrorsForPath)(errors, 'symbol') }),
                react_1.default.createElement(OperationCollection_1.OperationCollection, { operations: form.operations, onOperationsChange: function (operations) { return setFormValue('operations', operations); }, errors: (0, shared_1.filterErrors)(errors, "convert_from_standard") }),
                react_1.default.createElement(akeneo_design_system_1.Checkbox, { id: "measurements.unit.create_another", checked: createAnotherUnit, onChange: function (checked) { return setCreateAnotherUnit(checked); } }, translate('measurements.unit.create_another')))),
        react_1.default.createElement(akeneo_design_system_1.Modal.BottomButtons, null,
            react_1.default.createElement(akeneo_design_system_1.Button, { onClick: handleAdd, disabled: config.units_max <= measurementFamily.units.length }, translate('pim_common.add')))));
};
exports.CreateUnit = CreateUnit;
var templateObject_1;
//# sourceMappingURL=CreateUnit.js.map