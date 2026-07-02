var __makeTemplateObject = (this && this.__makeTemplateObject) || function (cooked, raw) {
    if (Object.defineProperty) { Object.defineProperty(cooked, "raw", { value: raw }); } else { cooked.raw = raw; }
    return cooked;
};
import React, { useCallback } from 'react';
import styled, { css } from 'styled-components';
import { getColor } from '../../theme';
var PAGINATION_SEPARATOR = '…';
var PaginationItem = function (_a) {
    var currentPage = _a.currentPage, page = _a.page, followPage = _a.followPage;
    var handleClick = useCallback(function () {
        if (page !== PAGINATION_SEPARATOR) {
            followPage(parseInt(page));
        }
    }, [page, followPage]);
    return (React.createElement(PaginationItemContainer, { onClick: handleClick, "data-testid": "paginationItem", title: page !== PAGINATION_SEPARATOR ? "No. ".concat(page) : '', disabled: page === PAGINATION_SEPARATOR, currentPage: currentPage, page: page, type: "button" }, page));
};
var currentPaginationItemMixin = css(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  border: 1px ", " solid;\n  color: ", ";\n"], ["\n  border: 1px ", " solid;\n  color: ", ";\n"])), getColor('brand', 100), getColor('brand', 100));
var otherPaginationItemMixin = css(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n  border: 1px ", " solid;\n  color: ", ";\n"], ["\n  border: 1px ", " solid;\n  color: ", ";\n"])), getColor('grey', 80), getColor('grey', 100));
var disabledMixin = css(templateObject_3 || (templateObject_3 = __makeTemplateObject(["\n  cursor: default;\n  :hover {\n    background-color: ", ";\n  }\n"], ["\n  cursor: default;\n  :hover {\n    background-color: ", ";\n  }\n"])), getColor('white'));
var PaginationItemContainer = styled.button(templateObject_4 || (templateObject_4 = __makeTemplateObject(["\n  ", "\n  display: inline-block;\n  border-width: 1px;\n  font-size: 13px;\n  font-weight: 400;\n  text-transform: uppercase;\n  border-radius: 16px;\n  padding: 0 10px;\n  height: 22px;\n  line-height: 21px;\n  cursor: pointer;\n  font-family: inherit;\n  transition: background-color 0.1s ease 0s;\n  min-width: 40px;\n  text-align: center;\n  box-sizing: border-box;\n  background-color: ", ";\n\n  :hover {\n    background-color: ", ";\n  }\n\n  :focus {\n    outline: 0;\n  }\n\n  ", "\n"], ["\n  ", "\n  display: inline-block;\n  border-width: 1px;\n  font-size: 13px;\n  font-weight: 400;\n  text-transform: uppercase;\n  border-radius: 16px;\n  padding: 0 10px;\n  height: 22px;\n  line-height: 21px;\n  cursor: pointer;\n  font-family: inherit;\n  transition: background-color 0.1s ease 0s;\n  min-width: 40px;\n  text-align: center;\n  box-sizing: border-box;\n  background-color: ", ";\n\n  :hover {\n    background-color: ", ";\n  }\n\n  :focus {\n    outline: 0;\n  }\n\n  ", "\n"])), function (_a) {
    var currentPage = _a.currentPage;
    return (currentPage ? currentPaginationItemMixin : otherPaginationItemMixin);
}, getColor('white'), getColor('grey', 20), function (_a) {
    var disabled = _a.disabled;
    return (disabled ? disabledMixin : null);
});
export { PaginationItem, PAGINATION_SEPARATOR };
var templateObject_1, templateObject_2, templateObject_3, templateObject_4;
//# sourceMappingURL=PaginationItem.js.map