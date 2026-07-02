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
import React, { isValidElement } from 'react';
import styled from 'styled-components';
import { getColor, getFontSize } from '../../theme';
var FigureContainer = styled.div(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  color: ", ";\n  font-size: 16px;\n  margin: 0 15px 0 3px;\n  display: flex;\n  align-items: center;\n\n  :only-child {\n    margin: 0;\n  }\n"], ["\n  color: ", ";\n  font-size: 16px;\n  margin: 0 15px 0 3px;\n  display: flex;\n  align-items: center;\n\n  :only-child {\n    margin: 0;\n  }\n"])), getColor('brand', 100));
var Figure = function (_a) {
    var label = _a.label, children = _a.children;
    return (React.createElement(React.Fragment, null,
        label && "".concat(label, " "),
        React.createElement(FigureContainer, null, children)));
};
var KeyFigureContainer = styled.div(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n  height: 80px;\n  display: inline-flex;\n  box-sizing: border-box;\n  background: ", ";\n"], ["\n  height: 80px;\n  display: inline-flex;\n  box-sizing: border-box;\n  background: ", ";\n"])), getColor('white'));
var IconContainer = styled.div(templateObject_3 || (templateObject_3 = __makeTemplateObject(["\n  min-width: 80px;\n  display: flex;\n  align-items: center;\n  justify-content: center;\n  border-right: 1px ", " solid;\n  margin: 10px 0;\n\n  svg {\n    color: ", ";\n  }\n"], ["\n  min-width: 80px;\n  display: flex;\n  align-items: center;\n  justify-content: center;\n  border-right: 1px ", " solid;\n  margin: 10px 0;\n\n  svg {\n    color: ", ";\n  }\n"])), getColor('grey', 80), getColor('grey', 100));
var ContentContainer = styled.div(templateObject_4 || (templateObject_4 = __makeTemplateObject(["\n  margin: 15px 20px;\n  display: flex;\n  justify-content: space-around;\n  flex-direction: column;\n  min-width: 0;\n"], ["\n  margin: 15px 20px;\n  display: flex;\n  justify-content: space-around;\n  flex-direction: column;\n  min-width: 0;\n"])));
var Title = styled.div(templateObject_5 || (templateObject_5 = __makeTemplateObject(["\n  color: ", ";\n  font-size: ", ";\n  overflow: hidden;\n  white-space: nowrap;\n  text-overflow: ellipsis;\n"], ["\n  color: ", ";\n  font-size: ", ";\n  overflow: hidden;\n  white-space: nowrap;\n  text-overflow: ellipsis;\n"])), getColor('grey', 140), getFontSize('big'));
var Values = styled.div(templateObject_6 || (templateObject_6 = __makeTemplateObject(["\n  display: flex;\n  color: ", ";\n  font-size: 16px;\n"], ["\n  display: flex;\n  color: ", ";\n  font-size: 16px;\n"])), getColor('grey', 100));
var KeyFigure = function (_a) {
    var icon = _a.icon, title = _a.title, children = _a.children, props = __rest(_a, ["icon", "title", "children"]);
    var validIcon = isValidElement(icon) && React.cloneElement(icon, { size: 30 });
    return (React.createElement(KeyFigureContainer, __assign({}, props),
        React.createElement(IconContainer, null, validIcon),
        React.createElement(ContentContainer, null,
            React.createElement(Title, null, title),
            React.createElement(Values, null, children))));
};
var KeyFigureGrid = styled.div(templateObject_7 || (templateObject_7 = __makeTemplateObject(["\n  display: grid;\n  grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));\n  gap: 20px;\n"], ["\n  display: grid;\n  grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));\n  gap: 20px;\n"])));
KeyFigure.Figure = Figure;
export { KeyFigure, KeyFigureGrid };
var templateObject_1, templateObject_2, templateObject_3, templateObject_4, templateObject_5, templateObject_6, templateObject_7;
//# sourceMappingURL=KeyFigure.js.map