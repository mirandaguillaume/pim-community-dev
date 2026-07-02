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
import React, { useEffect, useRef } from 'react';
import { createPortal } from 'react-dom';
import styled from 'styled-components';
import { CommonStyle, getColor, getFontSize } from '../../theme';
import { IconButton } from '../IconButton/IconButton';
import { CloseIcon } from '../../icons';
import { useShortcut } from '../../hooks';
import { Key } from '../../shared';
import { ModalContext, useInModal } from './ModalContext';
var ModalContainer = styled.div(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  ", "\n  position: fixed;\n  width: 100vw;\n  height: 100vh;\n  top: 0;\n  left: 0;\n  background-color: ", ";\n  display: flex;\n  flex-direction: column;\n  align-items: center;\n  justify-content: center;\n  z-index: 1800;\n  overflow: hidden;\n  padding: 20px 80px;\n  box-sizing: border-box;\n"], ["\n  ", "\n  position: fixed;\n  width: 100vw;\n  height: 100vh;\n  top: 0;\n  left: 0;\n  background-color: ", ";\n  display: flex;\n  flex-direction: column;\n  align-items: center;\n  justify-content: center;\n  z-index: 1800;\n  overflow: hidden;\n  padding: 20px 80px;\n  box-sizing: border-box;\n"])), CommonStyle, getColor('white'));
var ModalCloseButton = styled(IconButton)(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n  position: fixed;\n  top: 40px;\n  left: 40px;\n"], ["\n  position: fixed;\n  top: 40px;\n  left: 40px;\n"])));
var ModalContent = styled.div(templateObject_3 || (templateObject_3 = __makeTemplateObject(["\n  display: grid;\n  grid-template-columns: 1fr 2fr;\n"], ["\n  display: grid;\n  grid-template-columns: 1fr 2fr;\n"])));
var ModalChildren = styled.div(templateObject_4 || (templateObject_4 = __makeTemplateObject(["\n  display: flex;\n  flex-direction: column;\n  padding: 20px 40px;\n  min-width: 480px;\n  border-left: 1px solid ", ";\n"], ["\n  display: flex;\n  flex-direction: column;\n  padding: 20px 40px;\n  min-width: 480px;\n  border-left: 1px solid ", ";\n"])), getColor('brand', 100));
var IconContainer = styled.div(templateObject_5 || (templateObject_5 = __makeTemplateObject(["\n  display: flex;\n  justify-content: flex-end;\n  padding-right: 40px;\n"], ["\n  display: flex;\n  justify-content: flex-end;\n  padding-right: 40px;\n"])));
var SectionTitle = styled.div(templateObject_6 || (templateObject_6 = __makeTemplateObject(["\n  height: 20px;\n  color: ", ";\n  font-size: ", ";\n  text-transform: uppercase;\n"], ["\n  height: 20px;\n  color: ", ";\n  font-size: ", ";\n  text-transform: uppercase;\n"])), function (_a) {
    var color = _a.color;
    return getColor(color !== null && color !== void 0 ? color : 'grey', 120);
}, function (_a) {
    var size = _a.size;
    return getFontSize(size !== null && size !== void 0 ? size : 'default');
});
var Title = styled.div(templateObject_7 || (templateObject_7 = __makeTemplateObject(["\n  display: flex;\n  align-items: center;\n  height: 40px;\n  color: ", ";\n  font-size: ", ";\n  margin-bottom: 10px;\n"], ["\n  display: flex;\n  align-items: center;\n  height: 40px;\n  color: ", ";\n  font-size: ", ";\n  margin-bottom: 10px;\n"])), getColor('grey', 140), getFontSize('title'));
var BottomButtons = styled.div(templateObject_8 || (templateObject_8 = __makeTemplateObject(["\n  display: flex;\n  gap: 10px;\n  margin-top: 20px;\n"], ["\n  display: flex;\n  gap: 10px;\n  margin-top: 20px;\n"])));
var TopRightButtons = styled(BottomButtons)(templateObject_9 || (templateObject_9 = __makeTemplateObject(["\n  position: fixed;\n  top: 40px;\n  right: 40px;\n  margin: 0;\n"], ["\n  position: fixed;\n  top: 40px;\n  right: 40px;\n  margin: 0;\n"])));
var TopLeftButtons = styled(BottomButtons)(templateObject_10 || (templateObject_10 = __makeTemplateObject(["\n  position: fixed;\n  top: 40px;\n  left: 82px;\n  margin: 0;\n"], ["\n  position: fixed;\n  top: 40px;\n  left: 82px;\n  margin: 0;\n"])));
var Modal = function (_a) {
    var onClose = _a.onClose, illustration = _a.illustration, closeTitle = _a.closeTitle, children = _a.children, rest = __rest(_a, ["onClose", "illustration", "closeTitle", "children"]);
    var portalNode = document.createElement('div');
    portalNode.setAttribute('id', 'modal-root');
    var containerRef = useRef(portalNode);
    useShortcut(Key.Escape, onClose);
    useEffect(function () {
        document.body.appendChild(containerRef.current);
        return function () {
            document.body.removeChild(containerRef.current);
        };
    }, []);
    var stopEventPropagation = function (event) {
        event.stopPropagation();
    };
    return createPortal(React.createElement(ModalContext.Provider, { value: true },
        React.createElement(ModalContainer, __assign({ onClick: stopEventPropagation, role: "dialog" }, rest),
            React.createElement(ModalCloseButton, { title: closeTitle, level: "tertiary", ghost: "borderless", icon: React.createElement(CloseIcon, null), onClick: onClose }),
            undefined === illustration ? (children) : (React.createElement(ModalContent, null,
                React.createElement(IconContainer, null, React.cloneElement(illustration, { size: 220 })),
                React.createElement(ModalChildren, null, children))))), containerRef.current);
};
Modal.BottomButtons = BottomButtons;
Modal.TopRightButtons = TopRightButtons;
Modal.TopLeftButtons = TopLeftButtons;
Modal.Title = Title;
Modal.SectionTitle = SectionTitle;
export { Modal, useInModal };
var templateObject_1, templateObject_2, templateObject_3, templateObject_4, templateObject_5, templateObject_6, templateObject_7, templateObject_8, templateObject_9, templateObject_10;
//# sourceMappingURL=Modal.js.map