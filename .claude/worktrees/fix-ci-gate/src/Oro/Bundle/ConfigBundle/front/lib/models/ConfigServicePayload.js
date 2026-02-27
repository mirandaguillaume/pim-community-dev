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
Object.defineProperty(exports, "__esModule", { value: true });
exports.configBackToFront = exports.configFrontToBack = void 0;
function scopedValueBoolFrontToBack(sv) {
    return __assign(__assign({}, sv), { value: sv.value ? '1' : '0' });
}
function scopedValueBoolBackToFront(sv) {
    return __assign(__assign({}, sv), { value: sv.value === true || sv.value === '1' });
}
function configFrontToBack(config) {
    return __assign(__assign({}, config), { pim_analytics___version_update: scopedValueBoolFrontToBack(config.pim_analytics___version_update), pim_ui___loading_message_enabled: scopedValueBoolFrontToBack(config.pim_ui___loading_message_enabled), pim_ui___sandbox_banner: scopedValueBoolFrontToBack(config.pim_ui___sandbox_banner) });
}
exports.configFrontToBack = configFrontToBack;
function configBackToFront(config) {
    return __assign(__assign({}, config), { pim_analytics___version_update: scopedValueBoolBackToFront(config.pim_analytics___version_update), pim_ui___loading_message_enabled: scopedValueBoolBackToFront(config.pim_ui___loading_message_enabled), pim_ui___sandbox_banner: scopedValueBoolBackToFront(config.pim_ui___sandbox_banner) });
}
exports.configBackToFront = configBackToFront;
//# sourceMappingURL=ConfigServicePayload.js.map