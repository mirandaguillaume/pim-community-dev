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
import styled, { css } from 'styled-components';
import { CommonStyle, getColor } from '../../theme';
import { Checkbox } from '../Checkbox/Checkbox';
import { ArrowRightIcon, FolderIcon, FolderPlainIcon, FoldersIcon, FoldersPlainIcon, LoaderIcon } from '../../icons';
var folderIconCss = css(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  vertical-align: middle;\n  transition: color 0.2s ease;\n  margin-right: 5px;\n"], ["\n  vertical-align: middle;\n  transition: color 0.2s ease;\n  margin-right: 5px;\n"])));
var TreeContainer = styled.li(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n  display: block;\n  color: ", ";\n"], ["\n  display: block;\n  color: ", ";\n"])), getColor('grey140'));
var SubTreesContainer = styled.ul(templateObject_3 || (templateObject_3 = __makeTemplateObject(["\n  margin: 0 0 0 20px;\n  padding: 0;\n"], ["\n  margin: 0 0 0 20px;\n  padding: 0;\n"])));
var TreeArrowIcon = styled(ArrowRightIcon)(templateObject_4 || (templateObject_4 = __makeTemplateObject(["\n  transform: rotate(", "deg);\n  transition: transform 0.2s ease-out;\n  vertical-align: middle;\n  color: ", ";\n  cursor: pointer;\n"], ["\n  transform: rotate(", "deg);\n  transition: transform 0.2s ease-out;\n  vertical-align: middle;\n  color: ", ";\n  cursor: pointer;\n"])), function (_a) {
    var $isFolderOpen = _a.$isFolderOpen;
    return ($isFolderOpen ? '90' : '0');
}, getColor('grey100'));
var TreeLeafNotSelectedIcon = styled(FolderIcon)(templateObject_5 || (templateObject_5 = __makeTemplateObject(["\n  ", "\n"], ["\n  ", "\n"])), folderIconCss);
var TreeFolderSelectedIcon = styled(FoldersPlainIcon)(templateObject_6 || (templateObject_6 = __makeTemplateObject(["\n  ", "\n  color: ", ";\n"], ["\n  ", "\n  color: ", ";\n"])), folderIconCss, getColor('blue100'));
var TreeLeafSelectedIcon = styled(FolderPlainIcon)(templateObject_7 || (templateObject_7 = __makeTemplateObject(["\n  ", "\n  color: ", ";\n"], ["\n  ", "\n  color: ", ";\n"])), folderIconCss, getColor('blue100'));
var TreeFolderNotSelectedIcon = styled(FoldersIcon)(templateObject_8 || (templateObject_8 = __makeTemplateObject(["\n  ", "\n"], ["\n  ", "\n"])), folderIconCss);
var TreeLoaderIcon = styled(LoaderIcon)(templateObject_9 || (templateObject_9 = __makeTemplateObject(["\n  ", "\n  color: ", ";\n"], ["\n  ", "\n  color: ", ";\n"])), folderIconCss, getColor('grey100'));
var TreeLine = styled.div(templateObject_11 || (templateObject_11 = __makeTemplateObject(["\n  height: 40px;\n  line-height: 40px;\n  overflow: hidden;\n  width: 100%;\n  display: inline-flex;\n  align-items: center;\n  padding-right: 20px;\n  ", "\n"], ["\n  height: 40px;\n  line-height: 40px;\n  overflow: hidden;\n  width: 100%;\n  display: inline-flex;\n  align-items: center;\n  padding-right: 20px;\n  ", "\n"])), function (_a) {
    var $selected = _a.$selected;
    return $selected && css(templateObject_10 || (templateObject_10 = __makeTemplateObject(["\n      color: ", ";\n    "], ["\n      color: ", ";\n    "])), getColor('blue100'));
});
var NodeCheckbox = styled(Checkbox)(templateObject_12 || (templateObject_12 = __makeTemplateObject(["\n  display: inline-block;\n  vertical-align: middle;\n  margin-right: 8px;\n"], ["\n  display: inline-block;\n  vertical-align: middle;\n  margin-right: 8px;\n"])));
var ArrowButton = styled.button(templateObject_13 || (templateObject_13 = __makeTemplateObject(["\n  height: 30px;\n  width: 30px;\n  vertical-align: middle;\n  margin-right: 2px;\n  padding: 0;\n  border: none;\n  background: none;\n  &:not(:disabled) {\n    cursor: pointer;\n  }\n"], ["\n  height: 30px;\n  width: 30px;\n  vertical-align: middle;\n  margin-right: 2px;\n  padding: 0;\n  border: none;\n  background: none;\n  &:not(:disabled) {\n    cursor: pointer;\n  }\n"])));
var LabelWithFolder = styled.button(templateObject_16 || (templateObject_16 = __makeTemplateObject(["\n  ", "\n  height: 30px;\n  vertical-align: middle;\n  background: none;\n  border: none;\n  cursor: pointer;\n  padding: 0 5px 0 0;\n  cursor: pointer;\n  text-overflow: ellipsis;\n  overflow: hidden;\n  max-width: calc(100% - 35px);\n  text-align: left;\n  white-space: nowrap;\n  ", "\n  &:hover {\n    ", "\n  }\n"], ["\n  ", "\n  height: 30px;\n  vertical-align: middle;\n  background: none;\n  border: none;\n  cursor: pointer;\n  padding: 0 5px 0 0;\n  cursor: pointer;\n  text-overflow: ellipsis;\n  overflow: hidden;\n  max-width: calc(100% - 35px);\n  text-align: left;\n  white-space: nowrap;\n  ", "\n  &:hover {\n    ", "\n  }\n"])), CommonStyle, function (_a) {
    var $selected = _a.$selected;
    return $selected && css(templateObject_14 || (templateObject_14 = __makeTemplateObject(["\n      color: ", ";\n    "], ["\n      color: ", ";\n    "])), getColor('blue100'));
}, function (_a) {
    var $selected = _a.$selected;
    return !$selected && css(templateObject_15 || (templateObject_15 = __makeTemplateObject(["\n        color: ", ";\n      "], ["\n        color: ", ";\n      "])), getColor('grey140'));
});
var TreeIcon = function (_a) {
    var isLoading = _a.isLoading, isLeaf = _a.isLeaf, selected = _a.selected;
    if (isLoading) {
        return React.createElement(TreeLoaderIcon, { size: 24 });
    }
    if (isLeaf) {
        return selected ? React.createElement(TreeLeafSelectedIcon, { size: 24 }) : React.createElement(TreeLeafNotSelectedIcon, { size: 24 });
    }
    return selected ? React.createElement(TreeFolderSelectedIcon, { size: 24 }) : React.createElement(TreeFolderNotSelectedIcon, { size: 24 });
};
var Tree = function (_a) {
    var label = _a.label, value = _a.value, children = _a.children, _b = _a.isLeaf, isLeaf = _b === void 0 ? false : _b, _c = _a.selected, selected = _c === void 0 ? false : _c, _d = _a.isLoading, isLoading = _d === void 0 ? false : _d, _e = _a.selectable, selectable = _e === void 0 ? false : _e, _f = _a.readOnly, readOnly = _f === void 0 ? false : _f, onChange = _a.onChange, onOpen = _a.onOpen, onClose = _a.onClose, onClick = _a.onClick, _g = _a._isRoot, _isRoot = _g === void 0 ? true : _g, rest = __rest(_a, ["label", "value", "children", "isLeaf", "selected", "isLoading", "selectable", "readOnly", "onChange", "onOpen", "onClose", "onClick", "_isRoot"]);
    var subTrees = [];
    React.Children.forEach(children, function (child) {
        if (!isValidElement(child)) {
            throw new Error('Tree component only accepts Tree as children');
        }
        subTrees.push(child);
    });
    var _h = React.useState(subTrees.length > 0), isOpen = _h[0], setOpen = _h[1];
    var handleOpen = React.useCallback(function () {
        setOpen(true);
        if (onOpen) {
            onOpen(value);
        }
    }, [onOpen, value]);
    var handleClose = React.useCallback(function () {
        setOpen(false);
        if (onClose) {
            onClose(value);
        }
    }, [onClose, value]);
    var handleArrowClick = React.useCallback(function () {
        if (isLeaf) {
            return;
        }
        isOpen ? handleClose() : handleOpen();
    }, [isOpen, handleClose, handleOpen, isLeaf]);
    var handleClick = React.useCallback(function () {
        if (onClick) {
            onClick(value);
        }
        else {
            handleArrowClick();
        }
    }, [handleArrowClick, onClick, value]);
    var handleSelect = React.useCallback(function (checked, event) {
        if (onChange) {
            onChange(value, checked, event);
        }
    }, [onChange, value]);
    var result = (React.createElement(TreeContainer, __assign({ role: "treeitem", "aria-expanded": isOpen }, rest),
        React.createElement(TreeLine, { "$selected": selected },
            React.createElement(ArrowButton, { disabled: isLeaf, role: "button", onClick: handleArrowClick }, !isLeaf && React.createElement(TreeArrowIcon, { "$isFolderOpen": isOpen, size: 14 })),
            selectable && React.createElement(NodeCheckbox, { checked: selected, onChange: handleSelect, readOnly: readOnly }),
            React.createElement(LabelWithFolder, { onClick: handleClick, "$selected": selected, title: label, "aria-selected": selected },
                React.createElement(TreeIcon, { isLoading: isLoading, isLeaf: isLeaf, selected: selected }),
                label)),
        isOpen && !isLeaf && subTrees.length > 0 && (React.createElement(SubTreesContainer, { role: "group" }, subTrees.map(function (subTree) {
            return React.cloneElement(subTree, {
                key: JSON.stringify(subTree.props.value),
                _isRoot: false,
            });
        })))));
    return _isRoot ? React.createElement("ul", { role: "tree" }, result) : result;
};
Tree.displayName = 'Tree';
export { Tree };
var templateObject_1, templateObject_2, templateObject_3, templateObject_4, templateObject_5, templateObject_6, templateObject_7, templateObject_8, templateObject_9, templateObject_10, templateObject_11, templateObject_12, templateObject_13, templateObject_14, templateObject_15, templateObject_16;
//# sourceMappingURL=Tree.js.map