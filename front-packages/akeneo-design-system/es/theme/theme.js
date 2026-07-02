var getColor = function (color, gradient) {
    return function (_a) {
        var theme = _a.theme;
        return theme.color["".concat(color).concat(gradient !== null && gradient !== void 0 ? gradient : '')];
    };
};
var getColorForLevel = function (level, gradient) {
    return function (_a) {
        var theme = _a.theme;
        return theme.color["".concat(theme.palette[level]).concat(gradient)];
    };
};
var getColorAlternative = function (color, gradient) {
    return function (_a) {
        var theme = _a.theme;
        return theme.colorAlternative["".concat(color).concat(gradient !== null && gradient !== void 0 ? gradient : '')];
    };
};
var getFontSize = function (fontSize) {
    return function (_a) {
        var theme = _a.theme;
        return theme.fontSize[fontSize];
    };
};
var getFontFamily = function (fontFamilyType) {
    return function (_a) {
        var theme = _a.theme;
        return theme.fontFamily[fontFamilyType];
    };
};
export { getColor, getFontFamily, getColorForLevel, getColorAlternative, getFontSize };
//# sourceMappingURL=theme.js.map