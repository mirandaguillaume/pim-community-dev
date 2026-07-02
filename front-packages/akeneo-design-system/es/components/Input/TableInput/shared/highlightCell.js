var __makeTemplateObject = (this && this.__makeTemplateObject) || function (cooked, raw) {
    if (Object.defineProperty) { Object.defineProperty(cooked, "raw", { value: raw }); } else { cooked.raw = raw; }
    return cooked;
};
import { getColor } from '../../../../theme';
import { css } from 'styled-components';
var highlightCell = css(templateObject_3 || (templateObject_3 = __makeTemplateObject(["\n  ", ";\n\n  ", ";\n"], ["\n  ", ";\n\n  ", ";\n"])), function (_a) {
    var highlighted = _a.highlighted, inError = _a.inError;
    return highlighted &&
        !inError && css(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n      background: ", ";\n      box-shadow: 0 0 0 1px ", ";\n    "], ["\n      background: ", ";\n      box-shadow: 0 0 0 1px ", ";\n    "])), getColor('green', 10), getColor('green', 80));
}, function (_a) {
    var inError = _a.inError;
    return inError && css(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n      background: ", ";\n      box-shadow: 0 0 0 1px ", ";\n    "], ["\n      background: ", ";\n      box-shadow: 0 0 0 1px ", ";\n    "])), getColor('red', 10), getColor('red', 80));
});
export { highlightCell };
var templateObject_1, templateObject_2, templateObject_3;
//# sourceMappingURL=highlightCell.js.map