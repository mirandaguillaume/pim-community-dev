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
import React from 'react';
import styled, { css } from 'styled-components';
import { Dropdown, useBooleanState, ProductCategoryIllustration, TextInput, SearchIcon, getColor, SwitcherButton, } from 'akeneo-design-system';
import { useTranslate } from '../../hooks';
var CategoryTreeSwitcherButtonContainer = styled.div(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  border-bottom: 1px solid ", ";\n  width: 100%;\n"], ["\n  border-bottom: 1px solid ", ";\n  width: 100%;\n"])), getColor('brand', 100));
var CategoryTreeSwitcherButton = styled(SwitcherButton)(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n  height: 40px;\n  width: 100%;\n"], ["\n  height: 40px;\n  width: 100%;\n"])));
var CategoryTreeSwitcherContainer = styled(Dropdown)(templateObject_3 || (templateObject_3 = __makeTemplateObject(["\n  width: 100%;\n"], ["\n  width: 100%;\n"])));
var SearchInput = styled(TextInput)(templateObject_4 || (templateObject_4 = __makeTemplateObject(["\n  border: 0;\n  padding-left: 24px;\n"], ["\n  border: 0;\n  padding-left: 24px;\n"])));
var InputSearchIcon = styled(SearchIcon)(templateObject_5 || (templateObject_5 = __makeTemplateObject(["\n  position: absolute;\n  z-index: 1;\n  top: 10px;\n"], ["\n  position: absolute;\n  z-index: 1;\n  top: 10px;\n"])));
var EmptyResultsContainer = styled.div(templateObject_6 || (templateObject_6 = __makeTemplateObject(["\n  padding: 15px 40px 30px;\n  text-align: center;\n"], ["\n  padding: 15px 40px 30px;\n  text-align: center;\n"])));
var DropdownItem = styled.span(templateObject_8 || (templateObject_8 = __makeTemplateObject(["\n  ", "\n"], ["\n  ", "\n"])), function (_a) {
    var $selected = _a.$selected;
    return $selected && css(templateObject_7 || (templateObject_7 = __makeTemplateObject(["\n      color: ", ";\n    "], ["\n      color: ", ";\n    "])), getColor('brand', 100));
});
var CategoryTreeSwitcher = function (_a) {
    var _b;
    var trees = _a.trees, onClick = _a.onClick, rest = __rest(_a, ["trees", "onClick"]);
    var translate = useTranslate();
    var _c = useBooleanState(), isOpen = _c[0], open = _c[1], close = _c[2];
    var selectedTreeLabel = (_b = (trees.find(function (tree) { return tree.selected; }) || trees[0])) === null || _b === void 0 ? void 0 : _b.label;
    var _d = React.useState(''), value = _d[0], setValue = _d[1];
    var filteredTrees = trees.filter(function (tree) {
        return tree.label.toLowerCase().includes(value.toLowerCase());
    });
    return (React.createElement(CategoryTreeSwitcherContainer, __assign({}, rest),
        React.createElement(CategoryTreeSwitcherButtonContainer, null,
            React.createElement(CategoryTreeSwitcherButton, { label: '', onClick: open, "aria-haspopup": "listbox" }, selectedTreeLabel)),
        isOpen && (React.createElement(Dropdown.Overlay, { verticalPosition: "down", onClose: close },
            React.createElement(Dropdown.Header, null,
                React.createElement(Dropdown.Title, null,
                    React.createElement(InputSearchIcon, { size: 20 }),
                    React.createElement(SearchInput, { type: "text", value: value, placeholder: translate('pim_common.search'), onChange: setValue, tabIndex: 1 }))),
            filteredTrees.length ? (React.createElement(Dropdown.ItemCollection, { role: "listbox" }, filteredTrees.map(function (tree) { return (React.createElement(Dropdown.Item, { role: "option", key: tree.code, onClick: function () {
                    onClick(tree.id);
                    close();
                } },
                React.createElement(DropdownItem, { "$selected": tree.selected }, tree.label))); }))) : (React.createElement(EmptyResultsContainer, null,
                React.createElement(ProductCategoryIllustration, { size: '100%' }),
                translate('pim_common.select2.no_match')))))));
};
export { CategoryTreeSwitcher };
var templateObject_1, templateObject_2, templateObject_3, templateObject_4, templateObject_5, templateObject_6, templateObject_7, templateObject_8;
//# sourceMappingURL=CategoryTreeSwitcher.js.map