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
import React, { Children, cloneElement, isValidElement, useRef } from 'react';
import styled from 'styled-components';
import { Overlay } from './Overlay/Overlay';
import { Item } from './Item/Item';
import { ItemCollection } from './ItemCollection/ItemCollection';
import { Header } from './Header/Header';
import { Title } from './Header/Title';
import { getColor } from '../../theme';
import { Surtitle } from './Surtitle/Surtitle';
var Section = styled.div(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  background: ", ";\n  color: ", ";\n  height: 34px;\n  line-height: 34px;\n  padding: 0 20px;\n  outline-style: none;\n  white-space: nowrap;\n  text-transform: uppercase;\n  margin-top: 10px;\n"], ["\n  background: ", ";\n  color: ", ";\n  height: 34px;\n  line-height: 34px;\n  padding: 0 20px;\n  outline-style: none;\n  white-space: nowrap;\n  text-transform: uppercase;\n  margin-top: 10px;\n"])), getColor('white'), getColor('grey', 100));
var DropdownContainer = styled.div(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n  position: relative;\n  display: inline-flex;\n"], ["\n  position: relative;\n  display: inline-flex;\n"])));
var Dropdown = function (_a) {
    var children = _a.children, rest = __rest(_a, ["children"]);
    var ref = useRef(null);
    var decoratedChildren = Children.map(children, function (child) {
        if (!isValidElement(child) || child.type !== Overlay)
            return child;
        return cloneElement(child, {
            parentRef: ref,
        });
    });
    return (React.createElement(DropdownContainer, __assign({ ref: ref }, rest), decoratedChildren));
};
Overlay.displayName = 'Dropdown.Overlay';
Header.displayName = 'Dropdown.Header';
Title.displayName = 'Dropdown.Title';
ItemCollection.displayName = 'Dropdown.ItemCollection';
Item.displayName = 'Dropdown.Item';
Section.displayName = 'Dropdown.Section';
Dropdown.Overlay = Overlay;
Dropdown.Header = Header;
Dropdown.Item = Item;
Dropdown.Section = Section;
Dropdown.Title = Title;
Dropdown.ItemCollection = ItemCollection;
Dropdown.Surtitle = Surtitle;
export { Dropdown };
var templateObject_1, templateObject_2;
//# sourceMappingURL=Dropdown.js.map