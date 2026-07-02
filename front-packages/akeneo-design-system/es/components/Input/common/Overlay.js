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
import React, { useEffect, useRef, useState } from 'react';
import { createPortal } from 'react-dom';
import styled from 'styled-components';
import { useVerticalPosition, useWindowResize } from '../../../hooks';
import { CommonStyle, getColor } from '../../../theme';
var OverlayContent = styled.div(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  ", "\n  background: ", ";\n  box-shadow: 0 0 4px 0 rgba(0, 0, 0, 0.3);\n  padding: 10px 0 10px 0;\n  position: fixed;\n  opacity: ", ";\n  transition: opacity 0.15s ease-in-out;\n  z-index: 2001;\n  top: ", "px;\n  left: ", "px;\n  width: ", "px;\n"], ["\n  ", "\n  background: ", ";\n  box-shadow: 0 0 4px 0 rgba(0, 0, 0, 0.3);\n  padding: 10px 0 10px 0;\n  position: fixed;\n  opacity: ", ";\n  transition: opacity 0.15s ease-in-out;\n  z-index: 2001;\n  top: ", "px;\n  left: ", "px;\n  width: ", "px;\n"])), CommonStyle, getColor('white'), function (_a) {
    var visible = _a.visible;
    return (visible ? 1 : 0);
}, function (_a) {
    var top = _a.top;
    return top;
}, function (_a) {
    var left = _a.left;
    return left;
}, function (_a) {
    var width = _a.width;
    return width;
});
var Backdrop = styled.div(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n  position: fixed;\n  top: 0;\n  left: 0;\n  right: 0;\n  bottom: 0;\n  z-index: 2000;\n"], ["\n  position: fixed;\n  top: 0;\n  left: 0;\n  right: 0;\n  bottom: 0;\n  z-index: 2000;\n"])));
var getOverlayPosition = function (verticalPosition, parentRef, elementRef) {
    if (undefined === parentRef ||
        undefined === elementRef ||
        null === parentRef.current ||
        null === elementRef.current) {
        return [0, 0, 0];
    }
    var parentRect = parentRef.current.getBoundingClientRect();
    var elementRect = elementRef.current.getBoundingClientRect();
    var top = 'up' === verticalPosition ? parentRect.top - elementRect.height : parentRect.bottom;
    var left = parentRect.left;
    var width = parentRect.width;
    return [top, left, width];
};
var Overlay = function (_a) {
    var verticalPosition = _a.verticalPosition, parentRef = _a.parentRef, onClose = _a.onClose, children = _a.children, rest = __rest(_a, ["verticalPosition", "parentRef", "onClose", "children"]);
    var portalNode = document.createElement('div');
    portalNode.setAttribute('id', 'input-overlay-root');
    var portalRef = useRef(portalNode);
    var overlayRef = useRef(null);
    var _b = useState(false), visible = _b[0], setVisible = _b[1];
    var _c = useState([0, 0, 0]), overlayPosition = _c[0], setOverlayPosition = _c[1];
    var overlayVerticalPosition = useVerticalPosition(overlayRef, verticalPosition);
    useWindowResize();
    useEffect(function () {
        setVisible(true);
        document.body.appendChild(portalRef.current);
        return function () {
            document.body.removeChild(portalRef.current);
        };
    }, []);
    React.useEffect(function () {
        setOverlayPosition(getOverlayPosition(overlayVerticalPosition, parentRef, overlayRef));
    }, [children, overlayVerticalPosition, parentRef, overlayRef]);
    var top = overlayPosition[0], left = overlayPosition[1], width = overlayPosition[2];
    return createPortal(React.createElement(React.Fragment, null,
        React.createElement(Backdrop, { "data-testid": "backdrop", onClick: onClose }),
        React.createElement(OverlayContent, __assign({ ref: overlayRef, visible: visible, top: top, left: left, width: width }, rest), children)), portalRef.current);
};
export { Overlay };
var templateObject_1, templateObject_2;
//# sourceMappingURL=Overlay.js.map