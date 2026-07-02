var __makeTemplateObject = (this && this.__makeTemplateObject) || function (cooked, raw) {
    if (Object.defineProperty) { Object.defineProperty(cooked, "raw", { value: raw }); } else { cooked.raw = raw; }
    return cooked;
};
import React, { useEffect, useState, useCallback, useRef } from 'react';
import styled, { keyframes } from 'styled-components';
import { getColor, getFontSize } from '../../theme';
import { CheckIcon, CloseIcon, DangerIcon, InfoIcon } from '../../icons';
import { useAutoFocus } from '../../hooks';
var IconContainer = styled.div(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  padding: 0 25px;\n  display: inline-flex;\n"], ["\n  padding: 0 25px;\n  display: inline-flex;\n"])));
var Progress = styled.svg.attrs(function (_a) {
    var ratio = _a.ratio;
    return ({
        style: { strokeDashoffset: "calc(100% * ".concat(Math.PI * ratio - Math.PI, ")") },
    });
})(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n  position: absolute;\n  overflow: visible;\n  top: -10%;\n  left: -10%;\n  width: 120%;\n  height: 120%;\n\n  circle {\n    fill: transparent;\n    stroke: ", ";\n    stroke-linecap: round;\n    stroke-width: 5%;\n    stroke-dasharray: calc(100% * ", ");\n    transform: rotate(-88deg);\n    transform-origin: 50% 50%;\n    transition: all 1s linear;\n  }\n"], ["\n  position: absolute;\n  overflow: visible;\n  top: -10%;\n  left: -10%;\n  width: 120%;\n  height: 120%;\n\n  circle {\n    fill: transparent;\n    stroke: ", ";\n    stroke-linecap: round;\n    stroke-width: 5%;\n    stroke-dasharray: calc(100% * ", ");\n    transform: rotate(-88deg);\n    transform-origin: 50% 50%;\n    transition: all 1s linear;\n  }\n"])), function (_a) {
    var level = _a.level;
    return getLevelColor(level);
}, Math.PI);
var Content = styled.div(templateObject_3 || (templateObject_3 = __makeTemplateObject(["\n  padding: 10px 20px;\n  font-size: ", ";\n  border-left: 1px solid;\n  flex: 1;\n  line-height: 1.5;\n\n  a {\n    color: ", ";\n  }\n"], ["\n  padding: 10px 20px;\n  font-size: ", ";\n  border-left: 1px solid;\n  flex: 1;\n  line-height: 1.5;\n\n  a {\n    color: ", ";\n  }\n"])), getFontSize('small'), getColor('grey', 140));
var Title = styled.div(templateObject_4 || (templateObject_4 = __makeTemplateObject(["\n  font-size: ", ";\n  margin-bottom: 4px;\n"], ["\n  font-size: ", ";\n  margin-bottom: 4px;\n"])), getFontSize('big'));
var Timer = styled.div(templateObject_5 || (templateObject_5 = __makeTemplateObject(["\n  font-weight: 100;\n"], ["\n  font-weight: 100;\n"])));
var Icon = styled(CloseIcon)(templateObject_6 || (templateObject_6 = __makeTemplateObject([""], [""])));
var CloseButton = styled.button(templateObject_7 || (templateObject_7 = __makeTemplateObject(["\n  position: relative;\n  width: 24px;\n  height: 24px;\n  color: ", ";\n  border: 0;\n  background: none;\n  cursor: pointer;\n  display: inline-flex;\n  font-size: ", ";\n\n  & > * {\n    position: absolute;\n    line-height: 24px;\n    width: 100%;\n    top: 0;\n    left: 0;\n    transition: opacity 0.2s ease-in-out;\n  }\n\n  ", " {\n    opacity: ", ";\n  }\n\n  :hover {\n    ", " {\n      opacity: 1;\n    }\n    ", " {\n      opacity: 0;\n    }\n  }\n"], ["\n  position: relative;\n  width: 24px;\n  height: 24px;\n  color: ", ";\n  border: 0;\n  background: none;\n  cursor: pointer;\n  display: inline-flex;\n  font-size: ", ";\n\n  & > * {\n    position: absolute;\n    line-height: 24px;\n    width: 100%;\n    top: 0;\n    left: 0;\n    transition: opacity 0.2s ease-in-out;\n  }\n\n  ", " {\n    opacity: ", ";\n  }\n\n  :hover {\n    ", " {\n      opacity: 1;\n    }\n    ", " {\n      opacity: 0;\n    }\n  }\n"])), getColor('grey', 100), getFontSize('bigger'), Icon, function (_a) {
    var showIcon = _a.showIcon;
    return (showIcon ? 1 : 0);
}, Icon, Timer);
var MessageBarHideAnimation = keyframes(templateObject_8 || (templateObject_8 = __makeTemplateObject(["\n  0% {\n    transform: translateX(0);\n  }\n  75% {\n    transform: translateX(calc(100% + 50px));\n    max-height: 150px;\n    opacity: 0;\n  }\n  100% {\n    transform: translateX(calc(100% + 50px));\n    max-height: 0;\n    opacity: 0;\n  }\n"], ["\n  0% {\n    transform: translateX(0);\n  }\n  75% {\n    transform: translateX(calc(100% + 50px));\n    max-height: 150px;\n    opacity: 0;\n  }\n  100% {\n    transform: translateX(calc(100% + 50px));\n    max-height: 0;\n    opacity: 0;\n  }\n"])));
var MessageBarDisplayAnimation = keyframes(templateObject_9 || (templateObject_9 = __makeTemplateObject(["\n  0% {\n    transform: translateX(calc(100% + 50px));\n  }\n  100% {\n    transform: translateX(0);\n  }\n"], ["\n  0% {\n    transform: translateX(calc(100% + 50px));\n  }\n  100% {\n    transform: translateX(0);\n  }\n"])));
var ANIMATION_DURATION = 1000;
var AnimateContainer = styled.div(templateObject_10 || (templateObject_10 = __makeTemplateObject(["\n  animation: ", "\n    ", "ms forwards;\n  max-height: 150px;\n"], ["\n  animation: ", "\n    ", "ms forwards;\n  max-height: 150px;\n"])), function (_a) {
    var unmounting = _a.unmounting;
    return (unmounting ? MessageBarHideAnimation : MessageBarDisplayAnimation);
}, ANIMATION_DURATION);
var AnimateMessageBar = function (_a) {
    var children = _a.children;
    if (children.type !== MessageBar) {
        throw new Error('Only MessageBar element can be passed to AnimateMessageBar');
    }
    var _b = useState(false), unmounting = _b[0], setUnmounting = _b[1];
    var onClose = function () {
        setTimeout(function () { return setUnmounting(true); }, 0);
        setTimeout(function () {
            children.props.onClose();
        }, ANIMATION_DURATION);
    };
    return React.createElement(AnimateContainer, { unmounting: unmounting }, React.cloneElement(children, { onClose: onClose }));
};
var Container = styled.div(templateObject_11 || (templateObject_11 = __makeTemplateObject(["\n  display: flex;\n  align-items: center;\n  min-width: 400px;\n  max-width: 500px;\n  padding: 10px 20px 10px 0;\n  box-shadow: 2px 4px 8px 0 rgba(9, 30, 66, 0.25);\n  background-color: ", ";\n\n  ", ", ", " {\n    color: ", ";\n  }\n\n  ", " {\n    border-color: ", ";\n  }\n"], ["\n  display: flex;\n  align-items: center;\n  min-width: 400px;\n  max-width: 500px;\n  padding: 10px 20px 10px 0;\n  box-shadow: 2px 4px 8px 0 rgba(9, 30, 66, 0.25);\n  background-color: ", ";\n\n  ", ", ", " {\n    color: ", ";\n  }\n\n  ", " {\n    border-color: ", ";\n  }\n"])), getColor('white'), Title, IconContainer, function (_a) {
    var level = _a.level;
    return getLevelColor(level);
}, Content, function (_a) {
    var level = _a.level;
    return getLevelColor(level);
});
var getLevelColor = function (level) {
    switch (level) {
        case 'info':
            return getColor('blue', 100);
        case 'success':
            return getColor('green', 100);
        case 'warning':
            return getColor('yellow', 120);
        case 'error':
            return getColor('red', 100);
    }
};
var getLevelDuration = function (level) {
    switch (level) {
        case 'success':
        case 'info':
        case 'warning':
            return 5;
        case 'error':
            return 8;
    }
};
var getLevelIcon = function (level) {
    switch (level) {
        case 'success':
            return React.createElement(CheckIcon, null);
        case 'info':
            return React.createElement(InfoIcon, null);
        case 'warning':
        case 'error':
            return React.createElement(DangerIcon, null);
    }
};
var useOver = function () {
    var _a = useState(false), over = _a[0], setOver = _a[1];
    var onMouseOver = useCallback(function () {
        setOver(true);
    }, []);
    var onMouseOut = useCallback(function () {
        setOver(false);
    }, []);
    return [over, onMouseOver, onMouseOut];
};
var MessageBar = function (_a) {
    var _b = _a.level, level = _b === void 0 ? 'info' : _b, title = _a.title, icon = _a.icon, dismissTitle = _a.dismissTitle, onClose = _a.onClose, children = _a.children;
    var duration = getLevelDuration(level);
    var _c = useState(duration), remaining = _c[0], setRemaining = _c[1];
    var _d = useOver(), over = _d[0], onMouseOver = _d[1], onMouseOut = _d[2];
    useEffect(function () {
        var intervalId = setInterval(function () {
            return setRemaining(function (remaining) {
                if (0 > remaining) {
                    clearInterval(intervalId);
                    onClose();
                    return remaining;
                }
                return remaining - 1;
            });
        }, 1000);
        if (over) {
            clearInterval(intervalId);
            return;
        }
        return function () { return clearInterval(intervalId); };
    }, [over]);
    useEffect(function () {
        setRemaining(function (remaining) { return remaining - 1; });
    }, []);
    var ref = useRef(null);
    useAutoFocus(ref);
    var countDownFinished = -1 === remaining;
    var remainingDisplay = countDownFinished ? '' : Math.min(remaining + 1, duration);
    return (React.createElement(Container, { ref: ref, tabIndex: -1, role: 'error' === level ? 'alert' : 'status', level: level, onMouseOver: onMouseOver, onMouseOut: onMouseOut },
        React.createElement(IconContainer, { "aria-hidden": "true" }, React.cloneElement(icon !== null && icon !== void 0 ? icon : getLevelIcon(level), { size: 24 })),
        React.createElement(Content, null,
            React.createElement(Title, null, title),
            children),
        React.createElement(CloseButton, { onClick: onClose, showIcon: countDownFinished, title: dismissTitle },
            React.createElement(Timer, { "aria-hidden": "true" },
                remainingDisplay,
                React.createElement(Progress, { ratio: Math.max(0, remaining / duration), level: level },
                    React.createElement("circle", { r: "50%", cx: "50%", cy: "50%" }))),
            React.createElement(Icon, { size: 24 }))));
};
export { MessageBar, AnimateMessageBar };
var templateObject_1, templateObject_2, templateObject_3, templateObject_4, templateObject_5, templateObject_6, templateObject_7, templateObject_8, templateObject_9, templateObject_10, templateObject_11;
//# sourceMappingURL=MessageBar.js.map