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
import React, { Children, cloneElement, isValidElement } from 'react';
import styled, { css } from 'styled-components';
import { getColor, getFontSize } from '../../theme';
import { Button, IconButton } from '../../components';
var SectionTitleContainer = styled.div(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n  display: flex;\n  width: 100%;\n  gap: 10px;\n  align-items: center;\n  height: 44px;\n  line-height: 44px;\n  border-bottom: 1px solid ", ";\n\n  ", "\n"], ["\n  display: flex;\n  width: 100%;\n  gap: 10px;\n  align-items: center;\n  height: 44px;\n  line-height: 44px;\n  border-bottom: 1px solid ", ";\n\n  ", "\n"])), getColor('grey', 140), function (_a) {
    var sticky = _a.sticky;
    return undefined !== sticky && css(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n      position: sticky;\n      top: ", "px;\n      background-color: ", ";\n      z-index: 9;\n    "], ["\n      position: sticky;\n      top: ", "px;\n      background-color: ", ";\n      z-index: 9;\n    "])), sticky, getColor('white'));
});
var TitleContainer = styled.h2(templateObject_3 || (templateObject_3 = __makeTemplateObject(["\n  color: ", ";\n  font-size: ", ";\n  font-weight: 400;\n  text-transform: ", ";\n  text-overflow: ellipsis;\n  white-space: nowrap;\n  overflow: hidden;\n"], ["\n  color: ", ";\n  font-size: ", ";\n  font-weight: 400;\n  text-transform: ", ";\n  text-overflow: ellipsis;\n  white-space: nowrap;\n  overflow: hidden;\n"])), getColor('grey', 140), getFontSize('big'), function (_a) {
    var level = _a.level;
    return ('primary' === level ? 'uppercase' : 'unset');
});
var Title = function (_a) {
    var _b = _a.level, level = _b === void 0 ? 'primary' : _b, rest = __rest(_a, ["level"]);
    return (React.createElement(TitleContainer, __assign({ as: 'secondary' === level ? 'h3' : 'h2', level: level }, rest)));
};
var Spacer = styled.div(templateObject_4 || (templateObject_4 = __makeTemplateObject(["\n  flex-grow: 1;\n"], ["\n  flex-grow: 1;\n"])));
var Separator = styled.div(templateObject_5 || (templateObject_5 = __makeTemplateObject(["\n  border-left: 1px solid ", ";\n  margin: 0 10px;\n  height: 24px;\n"], ["\n  border-left: 1px solid ", ";\n  margin: 0 10px;\n  height: 24px;\n"])), getColor('grey', 100));
var Information = styled.div(templateObject_6 || (templateObject_6 = __makeTemplateObject(["\n  font-size: ", ";\n  font-weight: normal;\n  color: ", ";\n  white-space: nowrap;\n"], ["\n  font-size: ", ";\n  font-weight: normal;\n  color: ", ";\n  white-space: nowrap;\n"])), getFontSize('default'), getColor('brand', 100));
var SectionTitle = function (_a) {
    var children = _a.children, rest = __rest(_a, ["children"]);
    var decoratedChildren = Children.map(children, function (child) {
        if (isValidElement(child) && child.type === IconButton) {
            return cloneElement(child, {
                level: 'tertiary',
                size: 'small',
                ghost: 'borderless',
            });
        }
        if (isValidElement(child) && child.type === Button) {
            return cloneElement(child, __assign({ level: 'tertiary', size: 'small', ghost: true }, child.props));
        }
        return child;
    });
    return React.createElement(SectionTitleContainer, __assign({}, rest), decoratedChildren);
};
SectionTitle.Title = Title;
SectionTitle.Spacer = Spacer;
SectionTitle.Separator = Separator;
SectionTitle.Information = Information;
export { SectionTitle };
var templateObject_1, templateObject_2, templateObject_3, templateObject_4, templateObject_5, templateObject_6;
//# sourceMappingURL=SectionTitle.js.map