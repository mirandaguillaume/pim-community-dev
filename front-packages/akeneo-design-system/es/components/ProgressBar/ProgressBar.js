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
import React, { forwardRef } from 'react';
import styled, { css, keyframes } from 'styled-components';
import { getColor, getColorForLevel, getFontSize } from '../../theme';
import { useId } from '../../hooks';
var ProgressBarContainer = styled.div(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  overflow: hidden;\n"], ["\n  overflow: hidden;\n"])));
var progressBarAnimation = keyframes(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n  from { background-position: 0 0; }\n  to { background-position: 20px 0; }\n"], ["\n  from { background-position: 0 0; }\n  to { background-position: 20px 0; }\n"])));
var Header = styled.div(templateObject_3 || (templateObject_3 = __makeTemplateObject(["\n  display: flex;\n  font-size: ", ";\n  justify-content: space-between;\n"], ["\n  display: flex;\n  font-size: ", ";\n  justify-content: space-between;\n"])), getFontSize('default'));
var Title = styled.div(templateObject_4 || (templateObject_4 = __makeTemplateObject(["\n  color: ", ";\n  padding-right: 20px;\n  white-space: nowrap;\n  overflow: hidden;\n  text-overflow: ellipsis;\n"], ["\n  color: ", ";\n  padding-right: 20px;\n  white-space: nowrap;\n  overflow: hidden;\n  text-overflow: ellipsis;\n"])), getColor('grey', 140));
var ProgressLabel = styled.div(templateObject_5 || (templateObject_5 = __makeTemplateObject(["\n  color: ", ";\n  white-space: nowrap;\n"], ["\n  color: ", ";\n  white-space: nowrap;\n"])), getColor('grey', 120));
var ProgressBarBackground = styled.div(templateObject_6 || (templateObject_6 = __makeTemplateObject(["\n  background: ", ";\n  height: ", ";\n  overflow: hidden;\n  position: relative;\n"], ["\n  background: ", ";\n  height: ", ";\n  overflow: hidden;\n  position: relative;\n"])), getColor('grey', 60), function (props) { return getHeightFromSize(props.size); });
var ProgressBarFill = styled.div.attrs(function (props) { return ({
    style: { width: "".concat(props.width, "%") },
}); })(templateObject_9 || (templateObject_9 = __makeTemplateObject(["\n  ", "\n\n  height: 100%;\n  left: 0;\n  position: absolute;\n  top: 0;\n  transition: width 0.3s;\n\n  ", "\n"], ["\n  ", "\n\n  height: 100%;\n  left: 0;\n  position: absolute;\n  top: 0;\n  transition: width 0.3s;\n\n  ", "\n"])), function (_a) {
    var level = _a.level, light = _a.light;
    return css(templateObject_7 || (templateObject_7 = __makeTemplateObject(["\n    background: ", ";\n  "], ["\n    background: ", ";\n  "])), getColorForLevel(level, light ? 60 : 100));
}, function (props) {
    return props.indeterminate && css(templateObject_8 || (templateObject_8 = __makeTemplateObject(["\n      background-image: linear-gradient(\n        315deg,\n        rgba(255, 255, 255, 0.6) 25%,\n        rgba(255, 255, 255, 0.4) 25%,\n        rgba(255, 255, 255, 0.4) 50%,\n        rgba(255, 255, 255, 0.6) 50%,\n        rgba(255, 255, 255, 0.6) 75%,\n        rgba(255, 255, 255, 0.4) 75%,\n        rgba(255, 255, 255, 0.4) 100%\n      );\n      background-size: 20px 20px;\n      transition: width 200ms ease;\n      animation: ", " 1s linear infinite;\n    "], ["\n      background-image: linear-gradient(\n        315deg,\n        rgba(255, 255, 255, 0.6) 25%,\n        rgba(255, 255, 255, 0.4) 25%,\n        rgba(255, 255, 255, 0.4) 50%,\n        rgba(255, 255, 255, 0.6) 50%,\n        rgba(255, 255, 255, 0.6) 75%,\n        rgba(255, 255, 255, 0.4) 75%,\n        rgba(255, 255, 255, 0.4) 100%\n      );\n      background-size: 20px 20px;\n      transition: width 200ms ease;\n      animation: ", " 1s linear infinite;\n    "])), progressBarAnimation);
});
var getHeightFromSize = function (size) {
    switch (size) {
        case 'large':
            return '10px';
        case 'small':
        default:
            return '4px';
    }
};
var computeWidthFromPercent = function (percent) {
    if (percent === 'indeterminate' || percent > 100) {
        return 100;
    }
    if (percent < 0) {
        return 0;
    }
    return percent;
};
var ProgressBar = forwardRef(function (_a, forwardedRef) {
    var level = _a.level, percent = _a.percent, title = _a.title, progressLabel = _a.progressLabel, _b = _a.light, light = _b === void 0 ? false : _b, _c = _a.size, size = _c === void 0 ? 'small' : _c, rest = __rest(_a, ["level", "percent", "title", "progressLabel", "light", "size"]);
    var labelId = useId('label_');
    var progressBarId = useId('progress_');
    var progressBarProps = {};
    if (percent !== 'indeterminate' && isNaN(percent)) {
        percent = 'indeterminate';
    }
    if (percent !== 'indeterminate') {
        progressBarProps['aria-valuenow'] = computeWidthFromPercent(percent);
        progressBarProps['aria-valuemin'] = 0;
        progressBarProps['aria-valuemax'] = 100;
    }
    if (title) {
        progressBarProps['aria-labelledby'] = labelId;
    }
    return (React.createElement(ProgressBarContainer, __assign({ ref: forwardedRef }, rest),
        (title || progressLabel) && (React.createElement(Header, null,
            React.createElement(Title, { title: title, id: labelId, htmlFor: progressBarId }, title),
            progressLabel && React.createElement(ProgressLabel, { title: progressLabel }, progressLabel))),
        React.createElement(ProgressBarBackground, __assign({ id: progressBarId, role: "progressbar" }, progressBarProps, { size: size }),
            React.createElement(ProgressBarFill, { level: level, light: light, indeterminate: percent === 'indeterminate', width: computeWidthFromPercent(percent) }))));
});
export { ProgressBar };
var templateObject_1, templateObject_2, templateObject_3, templateObject_4, templateObject_5, templateObject_6, templateObject_7, templateObject_8, templateObject_9;
//# sourceMappingURL=ProgressBar.js.map