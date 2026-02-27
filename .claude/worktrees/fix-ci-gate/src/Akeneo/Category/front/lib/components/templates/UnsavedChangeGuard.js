"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.UnsavedChangesGuard = void 0;
var jsx_runtime_1 = require("react/jsx-runtime");
var shared_1 = require("@akeneo-pim-community/shared");
var react_1 = require("react");
var react_router_1 = require("react-router");
var useSaveStatus_1 = require("../../hooks/useSaveStatus");
var providers_1 = require("../providers");
var SaveStatusProvider_1 = require("../providers/SaveStatusProvider");
var UnsavedChangesGuard = function () {
    var translate = (0, shared_1.useTranslate)();
    var globalStatus = (0, useSaveStatus_1.useSaveStatus)().globalStatus;
    var handleBeforeUnload = (0, react_1.useCallback)(function (event) {
        if (globalStatus !== SaveStatusProvider_1.Status.SAVED) {
            event.preventDefault();
            event.returnValue = translate('akeneo.category.template.attribute.settings.unsaved_changes');
        }
    }, [globalStatus, translate]);
    (0, react_1.useEffect)(function () {
        window.addEventListener('beforeunload', handleBeforeUnload);
        return function () {
            window.removeEventListener('beforeunload', handleBeforeUnload);
        };
    }, [handleBeforeUnload]);
    var _a = (0, react_1.useContext)(providers_1.CanLeavePageContext), setCanLeavePage = _a.setCanLeavePage, setLeavePageMessage = _a.setLeavePageMessage;
    (0, react_1.useEffect)(function () {
        if (globalStatus === SaveStatusProvider_1.Status.SAVED) {
            setCanLeavePage(true);
        }
        else {
            setCanLeavePage(false);
            setLeavePageMessage(translate('akeneo.category.template.attribute.settings.unsaved_changes'));
        }
    }, [globalStatus, setCanLeavePage, setLeavePageMessage, translate]);
    return ((0, jsx_runtime_1.jsx)(react_router_1.Prompt, { when: globalStatus !== SaveStatusProvider_1.Status.SAVED, message: translate('akeneo.category.template.attribute.settings.unsaved_changes') }, void 0));
};
exports.UnsavedChangesGuard = UnsavedChangesGuard;
//# sourceMappingURL=UnsavedChangeGuard.js.map