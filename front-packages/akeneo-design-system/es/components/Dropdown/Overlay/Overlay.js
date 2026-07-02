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
import { createPortal } from 'react-dom';
import styled, { css } from 'styled-components';
import { Key } from '../../../shared';
import { useHorizontalPosition, useShortcut, useVerticalPosition, useWindowResize, } from '../../../hooks';
import { CommonStyle, getColor } from '../../../theme';
var BORDER_SHADOW_OFFSET = 2;
var getWidthProperties = function (_a) {
    var fixedWidth = _a.fixedWidth;
    if (null !== fixedWidth) {
        return css(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n      width: ", "px;\n    "], ["\n      width: ", "px;\n    "])), fixedWidth);
    }
    return css(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n    min-width: 150px;\n    max-width: 400px;\n  "], ["\n    min-width: 150px;\n    max-width: 400px;\n  "])));
};
var Container = styled.div(templateObject_3 || (templateObject_3 = __makeTemplateObject(["\n  ", "\n  background: ", ";\n  box-shadow: 0 0 4px 0 rgba(0, 0, 0, 0.3);\n  padding: 10px 0;\n  position: fixed;\n  opacity: ", ";\n  transition: opacity 0.15s ease-in-out;\n  z-index: 1901;\n  top: ", "px;\n  left: ", "px;\n\n  ", "\n"], ["\n  ", "\n  background: ", ";\n  box-shadow: 0 0 4px 0 rgba(0, 0, 0, 0.3);\n  padding: 10px 0;\n  position: fixed;\n  opacity: ", ";\n  transition: opacity 0.15s ease-in-out;\n  z-index: 1901;\n  top: ", "px;\n  left: ", "px;\n\n  ", "\n"])), CommonStyle, getColor('white'), function (_a) {
    var visible = _a.visible;
    return (visible ? 1 : 0);
}, function (_a) {
    var top = _a.top;
    return top;
}, function (_a) {
    var left = _a.left;
    return left;
}, getWidthProperties);
var Backdrop = styled.div(templateObject_4 || (templateObject_4 = __makeTemplateObject(["\n  position: fixed;\n  top: 0;\n  left: 0;\n  right: 0;\n  bottom: 0;\n  z-index: 1900;\n"], ["\n  position: fixed;\n  top: 0;\n  left: 0;\n  right: 0;\n  bottom: 0;\n  z-index: 1900;\n"])));
var getOverlayPosition = function (verticalPosition, horizontalPosition, dropdownOpenerVisible, parentRef, elementRef) {
    if (undefined === parentRef ||
        undefined === elementRef ||
        null === parentRef.current ||
        null === elementRef.current) {
        return [0, 0];
    }
    var parentRect = parentRef.current.getBoundingClientRect();
    var elementRect = elementRef.current.getBoundingClientRect();
    var top = 'up' === verticalPosition
        ? parentRect.bottom - elementRect.height + BORDER_SHADOW_OFFSET
        : parentRect.top - BORDER_SHADOW_OFFSET;
    if (dropdownOpenerVisible) {
        top = 'up' === verticalPosition ? parentRect.top - elementRect.height : parentRect.bottom + 1;
    }
    var left = 'left' === horizontalPosition ? parentRect.right - elementRect.width : parentRect.left;
    return [top, left];
};
var Overlay = function (_a) {
    var _b, _c, _d;
    var verticalPosition = _a.verticalPosition, horizontalPosition = _a.horizontalPosition, _e = _a.dropdownOpenerVisible, dropdownOpenerVisible = _e === void 0 ? false : _e, _f = _a.fullWidth, fullWidth = _f === void 0 ? false : _f, parentRef = _a.parentRef, onClose = _a.onClose, children = _a.children, rest = __rest(_a, ["verticalPosition", "horizontalPosition", "dropdownOpenerVisible", "fullWidth", "parentRef", "onClose", "children"]);
    var _g = useState([0, 0]), overlayPosition = _g[0], setOverlayPosition = _g[1];
    var portalNode = document.createElement('div');
    portalNode.setAttribute('id', 'dropdown-root');
    var portalRef = useRef(portalNode);
    var overlayRef = useRef(null);
    var overlayVerticalPosition = useVerticalPosition(overlayRef, verticalPosition);
    var overlayHorizontalPosition = useHorizontalPosition(overlayRef, horizontalPosition);
    var _h = useState(false), visible = _h[0], setVisible = _h[1];
    useShortcut(Key.Escape, onClose);
    useWindowResize();
    useEffect(function () {
        setVisible(true);
        document.body.appendChild(portalRef.current);
        return function () {
            document.body.removeChild(portalRef.current);
        };
    }, []);
    useEffect(function () {
        setOverlayPosition(getOverlayPosition(overlayVerticalPosition, overlayHorizontalPosition, dropdownOpenerVisible, parentRef, overlayRef));
    }, [children, overlayVerticalPosition, overlayHorizontalPosition, parentRef, overlayRef, dropdownOpenerVisible]);
    var top = overlayPosition[0], left = overlayPosition[1];
    var parentWidth = (_d = (_c = (_b = parentRef === null || parentRef === void 0 ? void 0 : parentRef.current) === null || _b === void 0 ? void 0 : _b.getBoundingClientRect()) === null || _c === void 0 ? void 0 : _c.width) !== null && _d !== void 0 ? _d : null;
    return createPortal(React.createElement(React.Fragment, null,
        React.createElement(Backdrop, { "data-testid": "backdrop", onClick: onClose }),
        React.createElement(Container, __assign({ ref: overlayRef, visible: visible, top: top, left: left, fixedWidth: fullWidth ? parentWidth : null }, rest), children)), portalRef.current);
};
Overlay.displayName = 'Overlay';
export { Overlay };
var templateObject_1, templateObject_2, templateObject_3, templateObject_4;
//# sourceMappingURL=Overlay.js.map