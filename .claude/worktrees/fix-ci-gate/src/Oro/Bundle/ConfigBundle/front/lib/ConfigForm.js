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
exports.ConfigForm = void 0;
var react_1 = __importStar(require("react"));
var styled_components_1 = __importDefault(require("styled-components"));
var shared_1 = require("@akeneo-pim-community/shared");
var akeneo_design_system_1 = require("akeneo-design-system");
var LocaleSelector_1 = require("./components/LocaleSelector");
var hooks_1 = require("./hooks");
var PageContent = (0, styled_components_1.default)(shared_1.PageContent)(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  padding-bottom: 40px;\n"], ["\n  padding-bottom: 40px;\n"])));
var ConfigForm = function () {
    var translate = (0, shared_1.useTranslate)();
    var systemHref = (0, shared_1.useRoute)('pim_system_index');
    var configFetchResult = (0, hooks_1.useFetchConfig)();
    var systemConfiguration = (0, shared_1.useSystemConfiguration)();
    var isEnabled = (0, shared_1.useFeatureFlags)().isEnabled;
    var _a = (0, react_1.useState)(null), config = _a[0], setConfig = _a[1];
    var _b = (0, react_1.useState)(false), isModified = _b[0], setIsModified = _b[1];
    var modifyConfig = function (config) {
        setConfig(config);
        setIsModified(true);
    };
    var handleChange = (0, react_1.useCallback)(function (fieldName) {
        return function (value) {
            var _a;
            if (!config)
                return;
            modifyConfig(__assign(__assign({}, config), (_a = {}, _a[fieldName] = __assign(__assign({}, config[fieldName]), { value: value }), _a)));
        };
    }, [config]);
    var doSaveConfig = (0, hooks_1.useSaveConfig)();
    var handleSave = function () { return __awaiter(void 0, void 0, void 0, function () {
        var feedbackConfig;
        return __generator(this, function (_a) {
            switch (_a.label) {
                case 0:
                    if (config === null)
                        return [2];
                    return [4, doSaveConfig(config)];
                case 1:
                    feedbackConfig = _a.sent();
                    setConfig(feedbackConfig);
                    return [4, systemConfiguration.refresh()];
                case 2:
                    _a.sent();
                    setIsModified(false);
                    return [2];
            }
        });
    }); };
    (0, react_1.useEffect)(function () {
        if (configFetchResult.type === 'fetched') {
            setConfig(__assign({}, configFetchResult.payload));
        }
    }, [configFetchResult]);
    if (configFetchResult.type === 'error') {
        return (react_1.default.createElement(akeneo_design_system_1.Helper, { level: "error" },
            translate('Unexpected error occurred. Please contact system administrator.'),
            ": ",
            configFetchResult.message));
    }
    if (!config)
        return null;
    return (react_1.default.createElement(react_1.default.Fragment, null,
        react_1.default.createElement(shared_1.PageHeader, null,
            react_1.default.createElement(shared_1.PageHeader.Breadcrumb, null,
                react_1.default.createElement(akeneo_design_system_1.Breadcrumb, null,
                    react_1.default.createElement(akeneo_design_system_1.Breadcrumb.Step, { href: "#" + systemHref }, translate('pim_menu.tab.system')),
                    react_1.default.createElement(akeneo_design_system_1.Breadcrumb.Step, null, translate('pim_menu.item.configuration')))),
            react_1.default.createElement(shared_1.PageHeader.UserActions, null,
                react_1.default.createElement(shared_1.PimView, { viewName: "pim-menu-user-navigation", className: "AknTitleContainer-userMenuContainer AknTitleContainer-userMenu" })),
            react_1.default.createElement(shared_1.PageHeader.Actions, null,
                react_1.default.createElement(akeneo_design_system_1.Button, { className: "AknButton--apply", onClick: handleSave }, translate('pim_common.save'))),
            isModified && (react_1.default.createElement(shared_1.PageHeader.State, null,
                react_1.default.createElement(shared_1.UnsavedChanges, null))),
            react_1.default.createElement(shared_1.PageHeader.Title, null, translate('pim_menu.item.configuration'))),
        react_1.default.createElement(PageContent, null,
            react_1.default.createElement(shared_1.Section, null,
                react_1.default.createElement("div", null,
                    react_1.default.createElement(akeneo_design_system_1.SectionTitle, null,
                        react_1.default.createElement(akeneo_design_system_1.SectionTitle.Title, null, translate('oro_config.form.config.group.loading_message.title'))),
                    react_1.default.createElement(akeneo_design_system_1.Helper, { level: "info" }, translate('oro_config.form.config.group.loading_message.helper'))),
                react_1.default.createElement(akeneo_design_system_1.Field, { "data-testid": "loading_message__enabler", label: translate('oro_config.form.config.group.loading_message.fields.enabler.label') },
                    react_1.default.createElement(akeneo_design_system_1.BooleanInput, { readOnly: false, value: config.pim_ui___loading_message_enabled.value, yesLabel: translate('pim_common.yes'), noLabel: translate('pim_common.no'), onChange: handleChange('pim_ui___loading_message_enabled') })),
                react_1.default.createElement(akeneo_design_system_1.Field, { label: translate('oro_config.form.config.group.loading_message.fields.messages.label') },
                    react_1.default.createElement(akeneo_design_system_1.TextAreaInput, { readOnly: false, value: config.pim_ui___loading_messages.value, onChange: handleChange('pim_ui___loading_messages'), placeholder: translate('oro_config.form.config.group.loading_message.placeholder') })),
                react_1.default.createElement(akeneo_design_system_1.SectionTitle, null,
                    react_1.default.createElement(akeneo_design_system_1.SectionTitle.Title, null, translate('oro_config.form.config.group.localization.title'))),
                react_1.default.createElement(akeneo_design_system_1.Field, { label: translate('oro_config.form.config.group.localization.fields.system_locale.label') },
                    react_1.default.createElement(LocaleSelector_1.LocaleSelector, { value: config.pim_ui___language.value, onChange: handleChange('pim_ui___language') })),
                react_1.default.createElement("div", null,
                    react_1.default.createElement(akeneo_design_system_1.SectionTitle, null,
                        react_1.default.createElement(akeneo_design_system_1.SectionTitle.Title, null, translate('oro_config.form.config.group.notification.title'))),
                    react_1.default.createElement(akeneo_design_system_1.Helper, { level: "info" }, translate('oro_config.form.config.group.notification.helper'))),
                react_1.default.createElement(akeneo_design_system_1.Field, { "data-testid": "notification_message__enabler", label: translate('oro_config.form.config.group.notification.fields.enabler.label') },
                    react_1.default.createElement(akeneo_design_system_1.BooleanInput, { readOnly: false, value: config.pim_analytics___version_update.value, yesLabel: translate('pim_common.yes'), noLabel: translate('pim_common.no'), onChange: handleChange('pim_analytics___version_update') })),
                isEnabled('sandbox_banner') && (react_1.default.createElement(react_1.default.Fragment, null,
                    react_1.default.createElement(akeneo_design_system_1.SectionTitle, null,
                        react_1.default.createElement(akeneo_design_system_1.SectionTitle.Title, null, translate('oro_config.form.config.group.environment.title'))),
                    react_1.default.createElement(akeneo_design_system_1.Field, { label: translate('oro_config.form.config.group.environment.fields.sandbox_banner.label') },
                        react_1.default.createElement(akeneo_design_system_1.BooleanInput, { readOnly: false, value: config.pim_ui___sandbox_banner.value, yesLabel: translate('pim_common.yes'), noLabel: translate('pim_common.no'), onChange: handleChange('pim_ui___sandbox_banner') }))))))));
};
exports.ConfigForm = ConfigForm;
var templateObject_1;
//# sourceMappingURL=ConfigForm.js.map