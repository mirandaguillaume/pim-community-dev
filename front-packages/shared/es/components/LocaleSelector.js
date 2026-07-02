var __makeTemplateObject = (this && this.__makeTemplateObject) || function (cooked, raw) {
    if (Object.defineProperty) { Object.defineProperty(cooked, "raw", { value: raw }); } else { cooked.raw = raw; }
    return cooked;
};
import React from 'react';
import { Dropdown, useBooleanState, Locale as LocaleWithFlag, getColor, SwitcherButton, getFontSize, Pill, } from 'akeneo-design-system';
import styled, { css } from 'styled-components';
import { useTranslate } from '../hooks';
var DropdownContainer = styled(Dropdown)(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  text-transform: none;\n  font-size: ", ";\n  color: ", ";\n"], ["\n  text-transform: none;\n  font-size: ", ";\n  color: ", ";\n"])), getFontSize('default'), getColor('grey', 120));
var HighlightLocaleWithFlag = styled(LocaleWithFlag)(templateObject_3 || (templateObject_3 = __makeTemplateObject(["\n  ", "\n"], ["\n  ", "\n"])), function (_a) {
    var selected = _a.selected;
    return selected && css(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n      color: ", ";\n      font-style: italic;\n      font-weight: bold;\n    "], ["\n      color: ", ";\n      font-style: italic;\n      font-weight: bold;\n    "])), getColor('purple100'));
});
var LocaleDropdownItem = styled(Dropdown.Item)(templateObject_4 || (templateObject_4 = __makeTemplateObject(["\n  justify-content: space-between;\n"], ["\n  justify-content: space-between;\n"])));
var LocaleSelector = function (_a) {
    var value = _a.value, values = _a.values, completeValues = _a.completeValues, onChange = _a.onChange, _b = _a.inline, inline = _b === void 0 ? true : _b;
    var translate = useTranslate();
    var _c = useBooleanState(), isOpen = _c[0], open = _c[1], close = _c[2];
    var selectedLocale = values.find(function (locale) { return locale.code === value; }) || values[0];
    var handleChange = function (localeCode) { return onChange === null || onChange === void 0 ? void 0 : onChange(localeCode); };
    return (React.createElement(DropdownContainer, null,
        React.createElement(SwitcherButton, { label: translate('pim_common.locale'), onClick: open, inline: inline },
            React.createElement(HighlightLocaleWithFlag, { code: selectedLocale.code, languageLabel: selectedLocale.label })),
        isOpen && (React.createElement(Dropdown.Overlay, { onClose: close },
            React.createElement(Dropdown.Header, null,
                React.createElement(Dropdown.Title, null, translate('pim_common.locale'))),
            React.createElement(Dropdown.ItemCollection, null, values.map(function (locale) { return (React.createElement(LocaleDropdownItem, { "aria-selected": locale.code === value, key: locale.code, onClick: function () {
                    close();
                    handleChange(locale.code);
                } },
                React.createElement(HighlightLocaleWithFlag, { code: locale.code, languageLabel: locale.label, selected: locale.code === value }),
                completeValues && !completeValues.includes(locale.code) && (React.createElement(Pill, { level: "warning", "data-testid": "LocaleSelector.incomplete.".concat(locale.code) })))); }))))));
};
export { LocaleSelector };
var templateObject_1, templateObject_2, templateObject_3, templateObject_4;
//# sourceMappingURL=LocaleSelector.js.map