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
var __spreadArray = (this && this.__spreadArray) || function (to, from, pack) {
    if (pack || arguments.length === 2) for (var i = 0, l = from.length, ar; i < l; i++) {
        if (ar || !(i in from)) {
            if (!ar) ar = Array.prototype.slice.call(from, 0, i);
            ar[i] = from[i];
        }
    }
    return to.concat(ar || Array.prototype.slice.call(from));
};
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.Edit = void 0;
var react_1 = __importStar(require("react"));
var react_router_dom_1 = require("react-router-dom");
var styled_components_1 = __importDefault(require("styled-components"));
var akeneo_design_system_1 = require("akeneo-design-system");
var shared_1 = require("@akeneo-pim-community/shared");
var use_measurement_family_1 = require("../../hooks/use-measurement-family");
var unit_tab_1 = require("./unit-tab");
var PropertyTab_1 = require("./PropertyTab");
var measurement_family_1 = require("../../model/measurement-family");
var use_save_measurement_family_saver_1 = require("./hooks/use-save-measurement-family-saver");
var CreateUnit_1 = require("../create-unit/CreateUnit");
var use_unsaved_changes_1 = require("../../shared/hooks/use-unsaved-changes");
var unsaved_changes_context_1 = require("../../context/unsaved-changes-context");
var use_measurement_family_remover_1 = require("../../hooks/use-measurement-family-remover");
var config_context_1 = require("../../context/config-context");
var Tab;
(function (Tab) {
    Tab["Units"] = "units";
    Tab["Properties"] = "properties";
})(Tab || (Tab = {}));
var Container = styled_components_1.default.div(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  /* 70 = TabContainer height + margin */\n  height: calc(100% - 70px);\n  display: flex;\n"], ["\n  /* 70 = TabContainer height + margin */\n  height: calc(100% - 70px);\n  display: flex;\n"])));
var TabsContainer = styled_components_1.default.div(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n  margin-bottom: 20px;\n"], ["\n  margin-bottom: 20px;\n"])));
var Tabs = styled_components_1.default.div(templateObject_3 || (templateObject_3 = __makeTemplateObject(["\n  display: flex;\n  width: 100%;\n  height: 50px;\n  border-bottom: 1px solid ", ";\n"], ["\n  display: flex;\n  width: 100%;\n  height: 50px;\n  border-bottom: 1px solid ", ";\n"])), function (props) { return props.theme.color.grey80; });
var TabSelector = styled_components_1.default.div(templateObject_4 || (templateObject_4 = __makeTemplateObject(["\n  width: 90px;\n  padding: 13px 0;\n  cursor: pointer;\n  font-size: ", ";\n  color: ", ";\n  border-bottom: 3px solid ", ";\n  display: flex;\n  align-items: baseline;\n\n  > :last-child {\n    margin-left: 5px;\n  }\n"], ["\n  width: 90px;\n  padding: 13px 0;\n  cursor: pointer;\n  font-size: ", ";\n  color: ", ";\n  border-bottom: 3px solid ", ";\n  display: flex;\n  align-items: baseline;\n\n  > :last-child {\n    margin-left: 5px;\n  }\n"])), function (props) { return props.theme.fontSize.big; }, function (props) { return (props.isActive ? props.theme.color.purple100 : 'inherit'); }, function (props) { return (props.isActive ? props.theme.color.purple100 : 'transparent'); });
var Errors = function (_a) {
    var errors = _a.errors;
    if (0 === errors.length) {
        return null;
    }
    return (react_1.default.createElement(react_1.default.Fragment, null, errors.map(function (error, index) { return (react_1.default.createElement(akeneo_design_system_1.Helper, { level: "error", key: index }, error.message)); })));
};
var Edit = function () {
    var translate = (0, shared_1.useTranslate)();
    var notify = (0, shared_1.useNotify)();
    var history = (0, react_router_dom_1.useHistory)();
    var locale = (0, shared_1.useUserContext)().get('uiLocale');
    var isGranted = (0, shared_1.useSecurity)().isGranted;
    var config = (0, react_1.useContext)(config_context_1.ConfigContext);
    var measurementFamilyCode = (0, react_router_dom_1.useParams)().measurementFamilyCode;
    var _a = (0, react_1.useState)(Tab.Units), currentTab = _a[0], setCurrentTab = _a[1];
    var _b = (0, use_measurement_family_1.useMeasurementFamily)(measurementFamilyCode), measurementFamily = _b[0], setMeasurementFamily = _b[1];
    var _c = (0, react_1.useState)(null), selectedUnitCode = _c[0], selectUnitCode = _c[1];
    var _d = (0, react_1.useState)([]), errors = _d[0], setErrors = _d[1];
    var _e = (0, akeneo_design_system_1.useBooleanState)(false), isAddUnitModalOpen = _e[0], openAddUnitModal = _e[1], closeAddUnitModal = _e[2];
    var _f = (0, akeneo_design_system_1.useBooleanState)(false), isConfirmDeleteMeasurementFamilyModalOpen = _f[0], openConfirmDeleteMeasurementFamilyModal = _f[1], closeConfirmDeleteMeasurementFamilyModal = _f[2];
    var settingsHref = (0, shared_1.useRoute)('pim_settings_index');
    var router = (0, shared_1.useRouter)();
    var setHasUnsavedChanges = (0, react_1.useContext)(unsaved_changes_context_1.UnsavedChangesContext).setHasUnsavedChanges;
    var _g = (0, use_unsaved_changes_1.useUnsavedChanges)(measurementFamily, translate('pim_ui.flash.unsaved_changes')), isModified = _g[0], resetState = _g[1];
    (0, react_1.useEffect)(function () {
        setHasUnsavedChanges(isModified);
    }, [isModified, setHasUnsavedChanges]);
    (0, react_1.useEffect)(function () {
        if (undefined === (measurementFamily === null || measurementFamily === void 0 ? void 0 : measurementFamily.code)) {
            return;
        }
        selectUnitCode(measurementFamily.standard_unit_code);
    }, [measurementFamily === null || measurementFamily === void 0 ? void 0 : measurementFamily.code, measurementFamily === null || measurementFamily === void 0 ? void 0 : measurementFamily.standard_unit_code]);
    var saveMeasurementFamily = (0, use_save_measurement_family_saver_1.useSaveMeasurementFamilySaver)();
    var handleSaveMeasurementFamily = (0, react_1.useCallback)(function () { return __awaiter(void 0, void 0, void 0, function () {
        var response, error_1;
        return __generator(this, function (_a) {
            switch (_a.label) {
                case 0:
                    if (null === measurementFamily) {
                        return [2];
                    }
                    setErrors([]);
                    _a.label = 1;
                case 1:
                    _a.trys.push([1, 3, , 4]);
                    return [4, saveMeasurementFamily(measurementFamily)];
                case 2:
                    response = _a.sent();
                    switch (response.success) {
                        case true:
                            resetState();
                            notify(shared_1.NotificationLevel.SUCCESS, translate('measurements.family.save.flash.success'));
                            break;
                        case false:
                            setErrors(response.errors);
                            break;
                    }
                    return [3, 4];
                case 3:
                    error_1 = _a.sent();
                    console.error(error_1);
                    notify(shared_1.NotificationLevel.ERROR, translate('measurements.family.save.flash.error'));
                    return [3, 4];
                case 4: return [2];
            }
        });
    }); }, [measurementFamily, saveMeasurementFamily, notify, translate, setErrors, resetState]);
    var removeMeasurementFamily = (0, use_measurement_family_remover_1.useMeasurementFamilyRemover)();
    var handleRemoveMeasurementFamily = (0, react_1.useCallback)(function () { return __awaiter(void 0, void 0, void 0, function () {
        var response, error_2;
        return __generator(this, function (_a) {
            switch (_a.label) {
                case 0:
                    _a.trys.push([0, 2, , 3]);
                    return [4, removeMeasurementFamily(measurementFamilyCode)];
                case 1:
                    response = _a.sent();
                    switch (response) {
                        case use_measurement_family_remover_1.MeasurementFamilyRemoverResult.Success:
                            notify(shared_1.NotificationLevel.SUCCESS, translate('measurements.family.delete.flash.success'));
                            history.push('/');
                            break;
                        case use_measurement_family_remover_1.MeasurementFamilyRemoverResult.NotFound:
                        case use_measurement_family_remover_1.MeasurementFamilyRemoverResult.Unprocessable:
                            throw Error("Error while deleting the measurement family: " + response);
                    }
                    return [3, 3];
                case 2:
                    error_2 = _a.sent();
                    console.error(error_2);
                    notify(shared_1.NotificationLevel.ERROR, translate('measurements.family.delete.flash.error'));
                    return [3, 3];
                case 3: return [2];
            }
        });
    }); }, [measurementFamilyCode, removeMeasurementFamily, history, notify, translate]);
    var handleNewUnit = (0, react_1.useCallback)(function (unit) {
        if (null === measurementFamily) {
            return;
        }
        setMeasurementFamily((0, measurement_family_1.addUnit)(measurementFamily, unit));
        selectUnitCode(unit.code);
    }, [setMeasurementFamily, measurementFamily, selectUnitCode]);
    if (undefined === measurementFamilyCode || null === measurementFamily) {
        return null;
    }
    if (undefined === measurementFamily) {
        return (react_1.default.createElement(shared_1.FullScreenError, { title: translate('error.exception', { status_code: '404' }), message: translate('measurements.family.not_found'), code: 404 }));
    }
    var _h = (0, shared_1.partitionErrors)(errors, [
        function (error) { return error.propertyPath.startsWith('units'); },
        function (error) { return error.propertyPath.startsWith('code') || error.propertyPath.startsWith('labels'); },
    ]), unitsErrors = _h[0], propertiesErrors = _h[1], otherErrors = _h[2];
    var measurementFamilyLabel = (0, measurement_family_1.getMeasurementFamilyLabel)(measurementFamily, locale);
    return (react_1.default.createElement(react_1.default.Fragment, null,
        react_1.default.createElement(react_router_dom_1.Prompt, { when: isModified, message: function () { return translate('pim_ui.flash.unsaved_changes'); } }),
        isAddUnitModalOpen && (react_1.default.createElement(CreateUnit_1.CreateUnit, { measurementFamily: measurementFamily, onClose: closeAddUnitModal, onNewUnit: handleNewUnit })),
        isConfirmDeleteMeasurementFamilyModalOpen && (react_1.default.createElement(shared_1.DeleteModal, { title: translate('measurements.title.measurement'), onConfirm: handleRemoveMeasurementFamily, onCancel: closeConfirmDeleteMeasurementFamilyModal }, translate('measurements.family.delete.confirm'))),
        react_1.default.createElement(shared_1.PageHeader, { showPlaceholder: null === measurementFamily },
            react_1.default.createElement(shared_1.PageHeader.Breadcrumb, null,
                react_1.default.createElement(akeneo_design_system_1.Breadcrumb, null,
                    react_1.default.createElement(akeneo_design_system_1.Breadcrumb.Step, { onClick: function () { return router.redirect(settingsHref); } }, translate('pim_menu.tab.settings')),
                    react_1.default.createElement(akeneo_design_system_1.Breadcrumb.Step, { href: history.createHref({ pathname: '/' }) }, translate('pim_menu.item.measurements')),
                    react_1.default.createElement(akeneo_design_system_1.Breadcrumb.Step, null, measurementFamilyLabel))),
            react_1.default.createElement(shared_1.PageHeader.UserActions, null,
                react_1.default.createElement(shared_1.PimView, { className: "AknTitleContainer-userMenuContainer AknTitleContainer-userMenu", viewName: "pim-measurements-user-navigation" })),
            react_1.default.createElement(shared_1.PageHeader.Actions, null,
                isGranted('akeneo_measurements_measurement_family_delete') && !measurementFamily.is_locked && (react_1.default.createElement(shared_1.SecondaryActions, null,
                    react_1.default.createElement(shared_1.SecondaryActions.Item, { onClick: openConfirmDeleteMeasurementFamilyModal }, translate('measurements.family.delete.button')))),
                isGranted('akeneo_measurements_measurement_unit_add') && (react_1.default.createElement(akeneo_design_system_1.Button, { level: "secondary", ghost: true, onClick: openAddUnitModal, disabled: config.units_max <= measurementFamily.units.length }, translate('measurements.unit.add'))),
                (isGranted('akeneo_measurements_measurement_unit_edit') ||
                    isGranted('akeneo_measurements_measurement_family_edit_properties')) && (react_1.default.createElement(akeneo_design_system_1.Button, { onClick: handleSaveMeasurementFamily }, translate('pim_common.save')))),
            react_1.default.createElement(shared_1.PageHeader.Title, null, measurementFamilyLabel !== null && measurementFamilyLabel !== void 0 ? measurementFamilyLabel : measurementFamilyCode),
            react_1.default.createElement(shared_1.PageHeader.State, null, isModified && react_1.default.createElement(shared_1.UnsavedChanges, null))),
        react_1.default.createElement(shared_1.PageContent, null,
            react_1.default.createElement(TabsContainer, null,
                react_1.default.createElement(Tabs, null, Object.values(Tab).map(function (tab) { return (react_1.default.createElement(TabSelector, { key: tab, onClick: function () { return setCurrentTab(tab); }, isActive: currentTab === tab },
                    translate("measurements.family.tab." + tab),
                    tab === Tab.Units && 0 < unitsErrors.length && react_1.default.createElement(akeneo_design_system_1.Pill, { level: "danger" }),
                    tab === Tab.Properties && 0 < propertiesErrors.length && react_1.default.createElement(akeneo_design_system_1.Pill, { level: "danger" }))); })),
                react_1.default.createElement(Errors, { errors: __spreadArray(__spreadArray([], unitsErrors.filter(function (error) { return error.propertyPath === 'units'; }), true), otherErrors, true) }),
                measurementFamily.is_locked && react_1.default.createElement(akeneo_design_system_1.Helper, { level: "warning" }, translate('measurements.family.is_locked'))),
            react_1.default.createElement(Container, null,
                currentTab === Tab.Units && null !== selectedUnitCode && (react_1.default.createElement(unit_tab_1.UnitTab, { measurementFamily: measurementFamily, onMeasurementFamilyChange: setMeasurementFamily, errors: (0, shared_1.filterErrors)(unitsErrors, 'units'), selectedUnitCode: selectedUnitCode, selectUnitCode: selectUnitCode })),
                currentTab === Tab.Properties && (react_1.default.createElement(PropertyTab_1.PropertyTab, { measurementFamily: measurementFamily, onMeasurementFamilyChange: setMeasurementFamily, errors: propertiesErrors }))))));
};
exports.Edit = Edit;
var templateObject_1, templateObject_2, templateObject_3, templateObject_4;
//# sourceMappingURL=Edit.js.map