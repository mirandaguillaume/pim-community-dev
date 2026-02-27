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
exports.ChannelSelector = void 0;
var jsx_runtime_1 = require("react/jsx-runtime");
var shared_1 = require("@akeneo-pim-community/shared");
var akeneo_design_system_1 = require("akeneo-design-system");
var ChannelSelector = function (_a) {
    var value = _a.value, values = _a.values, onChange = _a.onChange;
    var translate = (0, shared_1.useTranslate)();
    var userContext = (0, shared_1.useUserContext)();
    var catalogLocale = userContext.get('catalogLocale');
    var _b = __read((0, akeneo_design_system_1.useBooleanState)(false), 3), isOpen = _b[0], open = _b[1], close = _b[2];
    var selectedChannel = values.find(function (channel) { return channel.code === value; });
    var handleChange = function (channelCode) { return onChange === null || onChange === void 0 ? void 0 : onChange(channelCode); };
    return ((0, jsx_runtime_1.jsxs)(akeneo_design_system_1.Dropdown, { children: [(0, jsx_runtime_1.jsx)(akeneo_design_system_1.SwitcherButton, __assign({ inline: true, onClick: function () {
                    open();
                }, label: translate('pim_common.channel') }, { children: (0, jsx_runtime_1.jsx)("span", __assign({ "data-testid": "ChannelSelector.selection" }, { children: selectedChannel && (0, shared_1.getLabel)(selectedChannel.labels, catalogLocale, "" + selectedChannel.code) }), void 0) }), void 0), isOpen && ((0, jsx_runtime_1.jsxs)(akeneo_design_system_1.Dropdown.Overlay, __assign({ verticalPosition: "down", onClose: close }, { children: [(0, jsx_runtime_1.jsx)(akeneo_design_system_1.Dropdown.Header, { children: (0, jsx_runtime_1.jsx)(akeneo_design_system_1.Dropdown.Title, { children: translate('pim_common.channel') }, void 0) }, void 0), (0, jsx_runtime_1.jsx)(akeneo_design_system_1.Dropdown.ItemCollection, { children: values.map(function (channel) {
                            return ((0, jsx_runtime_1.jsx)(akeneo_design_system_1.Dropdown.Item, __assign({ "aria-selected": channel.code === value, onClick: function () {
                                    close();
                                    handleChange(channel.code);
                                }, isActive: channel.code === (selectedChannel === null || selectedChannel === void 0 ? void 0 : selectedChannel.code) }, { children: (0, jsx_runtime_1.jsx)("span", { children: (0, shared_1.getLabel)(channel.labels, catalogLocale, "" + channel.code) }, channel.code) }), channel.code));
                        }) }, void 0)] }), void 0))] }, void 0));
};
exports.ChannelSelector = ChannelSelector;
//# sourceMappingURL=ChannelSelector.js.map