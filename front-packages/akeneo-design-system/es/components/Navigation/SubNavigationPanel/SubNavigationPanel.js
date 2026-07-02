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
var __rest = (this && this.__rest) || function (s, e) {
    var t = {};
    for (var p in s) if (Object.prototype.hasOwnProperty.call(s, p) && e.indexOf(p) < 0)
        t[p] = s[p];
    if (s != null && typeof Object.getOwnPropertySymbols === "function")
        for (var i = 0, p = Object.getOwnPropertySymbols(s); i < p.length; i++) {
            if (e.indexOf(p[i]) < 0 && Object.prototype.propertyIsEnumerable.call(s, p[i]))
                t[p[i]] = s[p[i]];
        }
    return t;
};
import React, { useLayoutEffect } from 'react';
import styled from 'styled-components';
import { PanelCloseIcon, PanelOpenIcon } from '../../../icons';
import { getColor } from '../../../theme';
var Container = styled.div(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  background-color: ", ";\n  border-right: 1px solid ", ";\n  box-sizing: border-box;\n  display: flex;\n  flex-direction: column;\n  height: 100%;\n  transition: width 0.3s linear;\n  width: ", ";\n"], ["\n  background-color: ", ";\n  border-right: 1px solid ", ";\n  box-sizing: border-box;\n  display: flex;\n  flex-direction: column;\n  height: 100%;\n  transition: width 0.3s linear;\n  width: ", ";\n"])), getColor('grey', 20), getColor('grey', 80), function (_a) {
    var isOpen = _a.isOpen;
    return (isOpen ? '280px' : '40px');
});
var Content = styled.div(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n  display: flex;\n  flex-direction: column;\n  flex-grow: 1;\n  overflow-x: hidden;\n  overflow-y: auto;\n  opacity: ", ";\n  transition: ", ";\n  padding: ", ";\n"], ["\n  display: flex;\n  flex-direction: column;\n  flex-grow: 1;\n  overflow-x: hidden;\n  overflow-y: auto;\n  opacity: ", ";\n  transition: ", ";\n  padding: ", ";\n"])), function (_a) {
    var isOpen = _a.isOpen;
    return (isOpen ? '1' : '0');
}, function (_a) {
    var isOpen = _a.isOpen;
    return (isOpen ? 'opacity 300ms linear 300ms' : 'none');
}, function (_a) {
    var noPadding = _a.noPadding;
    return (noPadding ? '0' : '30px');
});
var ToggleButton = styled.button(templateObject_3 || (templateObject_3 = __makeTemplateObject(["\n  align-items: center;\n  background: none;\n  border: none;\n  border-top: 1px solid ", ";\n  box-sizing: border-box;\n  cursor: pointer;\n  display: flex;\n  flex: 0 0 auto;\n  height: 54px;\n  padding: 0;\n  padding-left: 12.5px;\n\n  svg {\n    color: ", ";\n    width: 15px;\n  }\n"], ["\n  align-items: center;\n  background: none;\n  border: none;\n  border-top: 1px solid ", ";\n  box-sizing: border-box;\n  cursor: pointer;\n  display: flex;\n  flex: 0 0 auto;\n  height: 54px;\n  padding: 0;\n  padding-left: 12.5px;\n\n  svg {\n    color: ", ";\n    width: 15px;\n  }\n"])), getColor('grey', 80), getColor('grey', 100));
var Collapsed = styled.div(templateObject_4 || (templateObject_4 = __makeTemplateObject(["\n  padding: 10px 5px;\n"], ["\n  padding: 10px 5px;\n"])));
var SubNavigationPanel = React.forwardRef(function (_a, forwardedRef) {
    var children = _a.children, _b = _a.isOpen, isOpen = _b === void 0 ? true : _b, open = _a.open, close = _a.close, _c = _a.closeTitle, closeTitle = _c === void 0 ? '' : _c, _d = _a.openTitle, openTitle = _d === void 0 ? '' : _d, _e = _a.noPadding, noPadding = _e === void 0 ? false : _e, rest = __rest(_a, ["children", "isOpen", "open", "close", "closeTitle", "openTitle", "noPadding"]);
    var collapsedElements = [];
    var contentElements = [];
    React.Children.forEach(children, function (child) {
        if (React.isValidElement(child) && child.type === Collapsed) {
            collapsedElements.push(child);
        }
        else {
            contentElements.push(child);
        }
    });
    var _f = React.useState(isOpen), isOpenTransition = _f[0], setIsOpenTransition = _f[1];
    useLayoutEffect(function () {
        setIsOpenTransition(isOpen);
    }, [isOpen]);
    return (React.createElement(Container, __assign({ ref: forwardedRef, isOpen: isOpen }, rest),
        !isOpen && collapsedElements,
        React.createElement(Content, { isOpen: isOpenTransition, noPadding: noPadding }, isOpen && contentElements),
        React.createElement(ToggleButton, { isOpen: isOpen, onClick: function () { return (isOpen ? close() : open()); }, title: isOpen ? closeTitle : openTitle, "data-testid": "open-subnavigation-button" }, isOpen ? React.createElement(PanelCloseIcon, null) : React.createElement(PanelOpenIcon, null))));
});
SubNavigationPanel.displayName = 'SubNavigationPanel';
Collapsed.displayName = 'SubNavigationPanel.Collapsed';
SubNavigationPanel.Collapsed = Collapsed;
export { SubNavigationPanel };
var templateObject_1, templateObject_2, templateObject_3, templateObject_4;
//# sourceMappingURL=SubNavigationPanel.js.map