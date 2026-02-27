"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.useUnsavedChanges = void 0;
var react_1 = require("react");
var useUnsavedChanges = function (entity, beforeUnloadMessage) {
    var _a = (0, react_1.useState)(false), isModified = _a[0], setModified = _a[1];
    var _b = (0, react_1.useState)(null), initialValue = _b[0], setInitialValue = _b[1];
    var updateValue = (0, react_1.useCallback)(function (newValue) {
        if (null === newValue) {
            return;
        }
        if (null === initialValue) {
            setInitialValue(JSON.stringify(newValue));
            return;
        }
        setModified(initialValue !== JSON.stringify(newValue));
    }, [setModified, setInitialValue, initialValue]);
    var resetValue = (0, react_1.useCallback)(function () {
        setInitialValue(JSON.stringify(entity));
        setModified(false);
    }, [setModified, setInitialValue, entity]);
    var handleUnload = (0, react_1.useCallback)(function (event) {
        if (!isModified) {
            return;
        }
        event.preventDefault();
        event.returnValue = beforeUnloadMessage;
        return beforeUnloadMessage;
    }, [isModified, beforeUnloadMessage]);
    (0, react_1.useEffect)(function () {
        window.addEventListener('beforeunload', handleUnload);
        return function () { return window.removeEventListener('beforeunload', handleUnload); };
    }, [handleUnload]);
    (0, react_1.useEffect)(function () {
        updateValue(entity);
    }, [updateValue, entity]);
    return [isModified, resetValue];
};
exports.useUnsavedChanges = useUnsavedChanges;
//# sourceMappingURL=use-unsaved-changes.js.map