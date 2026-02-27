"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.useSaveStatus = void 0;
var react_1 = require("react");
var SaveStatusProvider_1 = require("../components/providers/SaveStatusProvider");
var useSaveStatus = function () {
    var context = (0, react_1.useContext)(SaveStatusProvider_1.SaveStatusContext);
    if (null === context) {
        throw new Error('useSaveStatus must be used within a SaveStatusProvider');
    }
    return context;
};
exports.useSaveStatus = useSaveStatus;
//# sourceMappingURL=useSaveStatus.js.map