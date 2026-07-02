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
import React from 'react';
import styled from 'styled-components';
import { Helper, InfoIcon, getColor } from 'akeneo-design-system';
import { useFeatureFlags, useSystemConfiguration, useTranslate } from '../../hooks';
var HELPER_BACKGROUND_COLOR = '#5e63b6';
var WhiteInfoIcon = styled(InfoIcon)(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  color: ", ";\n"], ["\n  color: ", ";\n"])), getColor('white'));
var DarkBlueHelper = styled(Helper)(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n  background-color: ", ";\n  color: ", ";\n  position: sticky;\n  top: 0;\n  z-index: 20;\n"], ["\n  background-color: ", ";\n  color: ", ";\n  position: sticky;\n  top: 0;\n  z-index: 20;\n"])), HELPER_BACKGROUND_COLOR, getColor('white'));
var SandboxHelper = function (props) {
    var translate = useTranslate();
    var isEnabled = useFeatureFlags().isEnabled;
    var shouldDisplayBanner = isEnabled('sandbox_banner') && true === useSystemConfiguration().get('sandbox_banner');
    if (!shouldDisplayBanner) {
        return null;
    }
    return (React.createElement(DarkBlueHelper, __assign({ level: "info", icon: React.createElement(WhiteInfoIcon, null) }, props), translate('pim_system.sandbox.helper.text')));
};
export { SandboxHelper };
var templateObject_1, templateObject_2;
//# sourceMappingURL=SandboxHelper.js.map