var __makeTemplateObject = (this && this.__makeTemplateObject) || function (cooked, raw) {
    if (Object.defineProperty) { Object.defineProperty(cooked, "raw", { value: raw }); } else { cooked.raw = raw; }
    return cooked;
};
import styled from 'styled-components';
import { getColor, getFontSize } from 'akeneo-design-system';
var NoDataSection = styled.div(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  text-align: center;\n  margin-top: 70px;\n"], ["\n  text-align: center;\n  margin-top: 70px;\n"])));
var NoDataTitle = styled.div(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n  color: ", ";\n  font-size: ", ";\n  text-align: center;\n  margin: 30px 0 20px 0;\n"], ["\n  color: ", ";\n  font-size: ", ";\n  text-align: center;\n  margin: 30px 0 20px 0;\n"])), getColor('grey', 140), getFontSize('title'));
var NoDataText = styled.div(templateObject_3 || (templateObject_3 = __makeTemplateObject(["\n  color: ", ";\n  font-size: ", ";\n  text-align: center;\n"], ["\n  color: ", ";\n  font-size: ", ";\n  text-align: center;\n"])), getColor('grey', 120), getFontSize('bigger'));
export { NoDataSection, NoDataTitle, NoDataText };
var templateObject_1, templateObject_2, templateObject_3;
//# sourceMappingURL=NoData.js.map