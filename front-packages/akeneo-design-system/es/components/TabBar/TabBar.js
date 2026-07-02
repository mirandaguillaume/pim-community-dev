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
var __spreadArray = (this && this.__spreadArray) || function (to, from, pack) {
    if (pack || arguments.length === 2) for (var i = 0, l = from.length, ar; i < l; i++) {
        if (ar || !(i in from)) {
            if (!ar) ar = Array.prototype.slice.call(from, 0, i);
            ar[i] = from[i];
        }
    }
    return to.concat(ar || Array.prototype.slice.call(from));
};
import React, { Children, cloneElement, isValidElement, useEffect, useRef, useState, } from 'react';
import styled, { css } from 'styled-components';
import { getColor, getFontSize } from '../../theme';
import { Dropdown } from '../Dropdown/Dropdown';
import { IconButton } from '../IconButton/IconButton';
import { MoreIcon } from '../../icons';
import { useBooleanState } from '../../hooks';
import { Key } from '../../shared';
var Container = styled.div(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n  display: flex;\n  align-items: center;\n  border-bottom: 1px solid ", ";\n  background: ", ";\n\n  ", "\n"], ["\n  display: flex;\n  align-items: center;\n  border-bottom: 1px solid ", ";\n  background: ", ";\n\n  ", "\n"])), getColor('grey', 80), getColor('white'), function (_a) {
    var sticky = _a.sticky;
    return undefined !== sticky && css(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n      position: sticky;\n      top: ", "px;\n      background-color: ", ";\n      z-index: 9;\n    "], ["\n      position: sticky;\n      top: ", "px;\n      background-color: ", ";\n      z-index: 9;\n    "])), sticky, getColor('white'));
});
var TabBarContainer = styled.div(templateObject_3 || (templateObject_3 = __makeTemplateObject(["\n  display: flex;\n  gap: 10px;\n  flex-grow: 1;\n  height: 44px;\n  flex-wrap: wrap;\n  overflow: hidden;\n  margin-bottom: -1px;\n"], ["\n  display: flex;\n  gap: 10px;\n  flex-grow: 1;\n  height: 44px;\n  flex-wrap: wrap;\n  overflow: hidden;\n  margin-bottom: -1px;\n"])));
var TabContainer = styled.div(templateObject_4 || (templateObject_4 = __makeTemplateObject(["\n  display: flex;\n  align-items: center;\n  gap: 10px;\n  padding-right: 40px;\n  color: ", ";\n  border-bottom: 3px solid ", ";\n  font-size: ", ";\n  cursor: pointer;\n  white-space: nowrap;\n  height: 100%;\n  box-sizing: border-box;\n\n  &:hover {\n    color: ", ";\n    border-bottom: 3px solid ", ";\n  }\n"], ["\n  display: flex;\n  align-items: center;\n  gap: 10px;\n  padding-right: 40px;\n  color: ", ";\n  border-bottom: 3px solid ", ";\n  font-size: ", ";\n  cursor: pointer;\n  white-space: nowrap;\n  height: 100%;\n  box-sizing: border-box;\n\n  &:hover {\n    color: ", ";\n    border-bottom: 3px solid ", ";\n  }\n"])), function (_a) {
    var isActive = _a.isActive;
    return (isActive ? getColor('brand', 100) : getColor('grey', 100));
}, function (_a) {
    var isActive = _a.isActive;
    return (isActive ? getColor('brand', 100) : 'transparent');
}, getFontSize('big'), getColor('brand', 100), getColor('brand', 100));
var HiddenTabsDropdown = styled(Dropdown)(templateObject_5 || (templateObject_5 = __makeTemplateObject(["\n  border-bottom: 3px solid ", ";\n  margin-bottom: -1px;\n  height: 44px;\n  box-sizing: border-box;\n  align-items: center;\n\n  &:hover {\n    color: ", ";\n    border-bottom: 3px solid ", ";\n  }\n"], ["\n  border-bottom: 3px solid ", ";\n  margin-bottom: -1px;\n  height: 44px;\n  box-sizing: border-box;\n  align-items: center;\n\n  &:hover {\n    color: ", ";\n    border-bottom: 3px solid ", ";\n  }\n"])), function (_a) {
    var isActive = _a.isActive;
    return (isActive ? getColor('brand', 100) : 'transparent');
}, getColor('brand', 100), getColor('brand', 100));
var Tab = function (_a) {
    var children = _a.children, onClick = _a.onClick, isActive = _a.isActive, parentRef = _a.parentRef, onVisibilityChange = _a.onVisibilityChange, rest = __rest(_a, ["children", "onClick", "isActive", "parentRef", "onVisibilityChange"]);
    var ref = useRef(null);
    var handleKeyDown = function (event) {
        if (event.key === Key.Space || event.key === Key.Enter) {
            onClick === null || onClick === void 0 ? void 0 : onClick();
        }
    };
    useEffect(function () {
        if (undefined === parentRef) {
            throw new Error('TabBar.Tab can not be used outside TabBar');
        }
        var tabElement = ref.current;
        var tabBarElement = parentRef.current;
        if (null === tabElement)
            return;
        var options = {
            root: tabBarElement,
            rootMargin: '0px',
            threshold: 0,
        };
        var observer = new IntersectionObserver(function (entries) {
            var lastEntry = entries[entries.length - 1];
            onVisibilityChange === null || onVisibilityChange === void 0 ? void 0 : onVisibilityChange(lastEntry.isIntersecting);
        }, options);
        observer.observe(tabElement);
        return function () {
            observer.unobserve(tabElement);
        };
    }, []);
    return (React.createElement(TabContainer, __assign({ onKeyDown: handleKeyDown, onClick: onClick, ref: ref, tabIndex: 0, role: "tab", "aria-selected": isActive, isActive: isActive }, rest), children));
};
var TabBar = function (_a) {
    var moreButtonTitle = _a.moreButtonTitle, children = _a.children, rest = __rest(_a, ["moreButtonTitle", "children"]);
    var ref = useRef(null);
    var _b = useState([]), hiddenElements = _b[0], setHiddenElements = _b[1];
    var _c = useBooleanState(), isOpen = _c[0], open = _c[1], close = _c[2];
    var hiddenTabs = [];
    var decoratedChildren = Children.map(children, function (child, index) {
        if (!child) {
            return;
        }
        if (!isValidElement(child)) {
            throw new Error('TabBar only accepts TabBar.Tab as children');
        }
        var key = child.key !== null ? child.key : index;
        var isHidden = hiddenElements.includes(String(key));
        if (isHidden) {
            hiddenTabs.push(child);
        }
        return cloneElement(child, {
            parentRef: ref,
            tabIndex: isHidden ? -1 : 0,
            onVisibilityChange: function (isVisible) {
                setHiddenElements(function (previousHiddenElements) {
                    return isVisible
                        ? previousHiddenElements.filter(function (hiddenElement) { return hiddenElement !== String(key); })
                        : __spreadArray([String(key)], previousHiddenElements, true);
                });
            },
        });
    });
    var activeTabIsHidden = hiddenTabs.find(function (child) { return child.props.isActive; }) !== undefined;
    return (React.createElement(Container, __assign({}, rest),
        React.createElement(TabBarContainer, { ref: ref, role: "tablist" }, decoratedChildren),
        0 < hiddenTabs.length && (React.createElement(HiddenTabsDropdown, { isActive: activeTabIsHidden },
            React.createElement(IconButton, { level: "tertiary", ghost: "borderless", icon: React.createElement(MoreIcon, null), title: moreButtonTitle, onClick: open }),
            isOpen && (React.createElement(Dropdown.Overlay, { verticalPosition: "down", onClose: close },
                React.createElement(Dropdown.ItemCollection, null, hiddenTabs.map(function (child, index) {
                    var handleClick = function () {
                        var _a, _b;
                        close();
                        (_b = (_a = child.props).onClick) === null || _b === void 0 ? void 0 : _b.call(_a);
                    };
                    return (React.createElement(Dropdown.Item, { key: index, onClick: handleClick, isActive: child.props.isActive }, child.props.children));
                }))))))));
};
TabBar.Tab = Tab;
export { TabBar };
var templateObject_1, templateObject_2, templateObject_3, templateObject_4, templateObject_5;
//# sourceMappingURL=TabBar.js.map