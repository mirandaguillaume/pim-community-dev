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
import React, { useRef, useState, useEffect } from 'react';
import styled from 'styled-components';
import { getColor, getFontSize } from '../../theme';
import { IconButton } from '../../components';
import { CheckPartialIcon, PlusIcon } from '../../icons';
var ANIMATION_DURATION = 100;
var CollapseContainer = styled.div(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  width: 100%;\n  border: solid ", ";\n  border-width: 0 0 1px 0;\n\n  &:first-child {\n    border-width: 1px 0;\n  }\n  padding-bottom: ", ";\n"], ["\n  width: 100%;\n  border: solid ", ";\n  border-width: 0 0 1px 0;\n\n  &:first-child {\n    border-width: 1px 0;\n  }\n  padding-bottom: ", ";\n"])), getColor('grey', 40), function (_a) {
    var isOpen = _a.isOpen;
    return (isOpen ? '10px' : 0);
});
var Content = styled.div(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n  max-height: ", "px;\n  overflow: ", ";\n  ", "\n"], ["\n  max-height: ", "px;\n  overflow: ", ";\n  ", "\n"])), function (_a) {
    var $height = _a.$height;
    return $height;
}, function (_a) {
    var $overflow = _a.$overflow;
    return $overflow;
}, function (_a) {
    var shouldAnimate = _a.shouldAnimate;
    return shouldAnimate &&
        "\n    transition: max-height ".concat(ANIMATION_DURATION, "ms ease-in-out;\n  ");
});
var LabelContainer = styled.div(templateObject_3 || (templateObject_3 = __makeTemplateObject(["\n  height: 44px;\n  padding-right: 2px; // To manage the outline of the collapse icon being cropped in case of overflow hidden\n  display: flex;\n  align-items: center;\n  cursor: pointer;\n"], ["\n  height: 44px;\n  padding-right: 2px; // To manage the outline of the collapse icon being cropped in case of overflow hidden\n  display: flex;\n  align-items: center;\n  cursor: pointer;\n"])));
var Label = styled.div(templateObject_4 || (templateObject_4 = __makeTemplateObject(["\n  flex: 1;\n  text-transform: uppercase;\n  color: ", ";\n  font-size: ", ";\n  display: flex;\n  align-items: center;\n  gap: 10px;\n"], ["\n  flex: 1;\n  text-transform: uppercase;\n  color: ", ";\n  font-size: ", ";\n  display: flex;\n  align-items: center;\n  gap: 10px;\n"])), getColor('grey', 140), getFontSize('default'));
var Collapse = React.forwardRef(function (_a, forwardedRef) {
    var label = _a.label, collapseButtonLabel = _a.collapseButtonLabel, isOpen = _a.isOpen, onCollapse = _a.onCollapse, children = _a.children, rest = __rest(_a, ["label", "collapseButtonLabel", "isOpen", "onCollapse", "children"]);
    var _b = useState(0), contentHeight = _b[0], setContentHeight = _b[1];
    var _c = useState(false), shouldAnimate = _c[0], setShouldAnimate = _c[1];
    var contentRef = useRef(null);
    var handleCollapse = function () { return onCollapse(!isOpen); };
    useEffect(function () {
        setContentHeight(function (contentHeight) {
            var _a, _b;
            var scrollHeight = (_b = (_a = contentRef.current) === null || _a === void 0 ? void 0 : _a.scrollHeight) !== null && _b !== void 0 ? _b : 0;
            return 0 === scrollHeight ? contentHeight : scrollHeight;
        });
        var shouldAnimateTimeoutId = window.setTimeout(function () {
            setShouldAnimate(true);
        }, ANIMATION_DURATION);
        return function () {
            window.clearTimeout(shouldAnimateTimeoutId);
        };
    }, [children]);
    return (React.createElement(CollapseContainer, __assign({ ref: forwardedRef, isOpen: isOpen }, rest),
        React.createElement(LabelContainer, { onClick: handleCollapse },
            React.createElement(Label, null, label),
            React.createElement(IconButton, { size: "small", level: "tertiary", ghost: "borderless", title: collapseButtonLabel, icon: isOpen ? React.createElement(CheckPartialIcon, null) : React.createElement(PlusIcon, null) })),
        React.createElement(Content, { ref: contentRef, "$overflow": shouldAnimate || !isOpen ? 'hidden' : 'inherit', "$height": isOpen ? contentHeight : 0, shouldAnimate: shouldAnimate }, children)));
});
export { Collapse };
var templateObject_1, templateObject_2, templateObject_3, templateObject_4;
//# sourceMappingURL=Collapse.js.map