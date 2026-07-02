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
import React, { useMemo } from 'react';
import styled, { css } from 'styled-components';
import { useTheme } from '../../hooks';
import { getColor } from '../../theme';
var AvatarContainer = styled.span(templateObject_3 || (templateObject_3 = __makeTemplateObject(["\n  ", "\n  display: inline-block;\n  color: ", ";\n  text-align: center;\n  background-position: center;\n  background-repeat: no-repeat;\n  background-size: cover;\n  text-transform: uppercase;\n  cursor: ", ";\n"], ["\n  ", "\n  display: inline-block;\n  color: ", ";\n  text-align: center;\n  background-position: center;\n  background-repeat: no-repeat;\n  background-size: cover;\n  text-transform: uppercase;\n  cursor: ", ";\n"])), function (_a) {
    var size = _a.size;
    return size === 'default'
        ? css(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n          height: 32px;\n          width: 32px;\n          line-height: 32px;\n          font-size: 15px;\n          border-radius: 32px;\n        "], ["\n          height: 32px;\n          width: 32px;\n          line-height: 32px;\n          font-size: 15px;\n          border-radius: 32px;\n        "]))) : css(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n          height: 140px;\n          width: 140px;\n          line-height: 140px;\n          font-size: 66px;\n          border-radius: 140px;\n        "], ["\n          height: 140px;\n          width: 140px;\n          line-height: 140px;\n          font-size: 66px;\n          border-radius: 140px;\n        "])));
}, getColor('white'), function (_a) {
    var onClick = _a.onClick;
    return (onClick ? 'pointer' : 'default');
});
var Avatar = function (_a) {
    var username = _a.username, firstName = _a.firstName, lastName = _a.lastName, avatarUrl = _a.avatarUrl, _b = _a.size, size = _b === void 0 ? 'default' : _b, rest = __rest(_a, ["username", "firstName", "lastName", "avatarUrl", "size"]);
    var theme = useTheme();
    var fallback = (firstName.trim().charAt(0) + lastName.trim().charAt(0) || username.substring(0, 2)).toLocaleUpperCase();
    var title = "".concat(firstName || '', " ").concat(lastName || '').trim() || username;
    var backgroundColor = useMemo(function () {
        var colorId = username.split('').reduce(function (s, l) { return s + l.charCodeAt(0); }, 0);
        var colors = [
            theme.colorAlternative.green120,
            theme.colorAlternative.darkCyan120,
            theme.colorAlternative.forestGreen120,
            theme.colorAlternative.oliveGreen120,
            theme.colorAlternative.blue120,
            theme.colorAlternative.darkBlue120,
            theme.colorAlternative.hotPink120,
            theme.colorAlternative.red120,
            theme.colorAlternative.coralRed120,
            theme.colorAlternative.yellow120,
            theme.colorAlternative.orange120,
            theme.colorAlternative.chocolate120,
        ];
        return colors[colorId % colors.length];
    }, [theme, username]);
    var style = avatarUrl ? { backgroundImage: "url(".concat(avatarUrl, ")") } : { backgroundColor: backgroundColor };
    return (React.createElement(AvatarContainer, __assign({ size: size }, rest, { style: style, title: title }), avatarUrl ? '' : fallback));
};
export { Avatar };
var templateObject_1, templateObject_2, templateObject_3;
//# sourceMappingURL=Avatar.js.map