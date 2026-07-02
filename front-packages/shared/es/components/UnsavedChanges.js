var __makeTemplateObject = (this && this.__makeTemplateObject) || function (cooked, raw) {
    if (Object.defineProperty) { Object.defineProperty(cooked, "raw", { value: raw }); } else { cooked.raw = raw; }
    return cooked;
};
import React from 'react';
import styled from 'styled-components';
import { DangerPlainIcon, getColor } from 'akeneo-design-system';
import { useTranslate } from '../hooks';
var Container = styled.div(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  color: ", ";\n  font-style: italic;\n  border-bottom: 1px solid ", ";\n  white-space: nowrap;\n  margin-top: 6px;\n  display: flex;\n  align-items: center;\n  height: 17px;\n  gap: 3px;\n"], ["\n  color: ", ";\n  font-style: italic;\n  border-bottom: 1px solid ", ";\n  white-space: nowrap;\n  margin-top: 6px;\n  display: flex;\n  align-items: center;\n  height: 17px;\n  gap: 3px;\n"])), getColor('grey', 140), getColor('yellow', 100));
var Danger = styled(DangerPlainIcon)(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n  color: ", ";\n"], ["\n  color: ", ";\n"])), getColor('yellow', 100));
var UnsavedChanges = function () {
    var translate = useTranslate();
    return (React.createElement(Container, null,
        React.createElement(Danger, { size: 18 }),
        " ",
        translate('pim_common.entity_updated')));
};
export { UnsavedChanges };
var templateObject_1, templateObject_2;
//# sourceMappingURL=UnsavedChanges.js.map