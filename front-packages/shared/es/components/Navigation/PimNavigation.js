var __makeTemplateObject = (this && this.__makeTemplateObject) || function (cooked, raw) {
    if (Object.defineProperty) { Object.defineProperty(cooked, "raw", { value: raw }); } else { cooked.raw = raw; }
    return cooked;
};
import React, { useEffect, useMemo, useState } from 'react';
import styled, { css } from 'styled-components';
import { PimView } from '../PimView';
import { useRouter, useTranslate } from '../../hooks';
import { HelpIcon, LockIcon, MainNavigationItem, Tag, useTheme } from 'akeneo-design-system';
import { SubNavigation } from './SubNavigation';
import { useAnalytics } from '../../hooks';
var StyledMainNavigationItem = styled(MainNavigationItem)(templateObject_3 || (templateObject_3 = __makeTemplateObject(["\n  ", "\n\n  ", "\n"], ["\n  ", "\n\n  ", "\n"])), function (_a) {
    var align = _a.align;
    return align === 'bottom' && css(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n      position: absolute;\n      bottom: 0;\n    "], ["\n      position: absolute;\n      bottom: 0;\n    "])));
}, function (_a) {
    var disabled = _a.disabled, freeTrialEnabled = _a.freeTrialEnabled;
    return disabled &&
        freeTrialEnabled && css(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n      cursor: pointer;\n    "], ["\n      cursor: pointer;\n    "])));
});
var PimNavigation = function (_a) {
    var entries = _a.entries, activeEntryCode = _a.activeEntryCode, activeSubEntryCode = _a.activeSubEntryCode, _b = _a.freeTrialEnabled, freeTrialEnabled = _b === void 0 ? false : _b;
    var translate = useTranslate();
    var router = useRouter();
    var theme = useTheme();
    var analytics = useAnalytics();
    var _c = useState(), pimVersion = _c[0], setPimVersion = _c[1];
    var _d = useState(false), showHelpDropdown = _d[0], setShowHelpDropdown = _d[1];
    useEffect(function () {
        fetch(router.generate('pim_analytics_data_collect')).then(function (response) {
            if (response.ok) {
                response.json().then(function (data) {
                    setPimVersion(data);
                });
            }
        });
    }, []);
    var handleFollowEntry = function (event, entry) {
        event.stopPropagation();
        event.preventDefault();
        analytics.appcuesTrack('navigation:entry:clicked', {
            code: entry.code,
        });
        router.redirect(router.generate(entry.route));
    };
    var activeNavigationEntry = useMemo(function () {
        return entries.find(function (entry) { return entry.code === activeEntryCode; });
    }, [entries, activeEntryCode]);
    var activeSubNavigation = useMemo(function () {
        if (undefined === activeNavigationEntry) {
            return;
        }
        return activeNavigationEntry.subNavigations.find(function (column) {
            return undefined !== column.entries.find(function (entry) { return entry.code === activeSubEntryCode; });
        });
    }, [activeNavigationEntry, activeSubEntryCode]);
    var helpCenterUrl = useMemo(function () {
        if (!pimVersion)
            return 'https://help.akeneo.com';
        var isSerenity = (pimVersion === null || pimVersion === void 0 ? void 0 : pimVersion.pim_version.split('.').length) === 1;
        var version = isSerenity ? 'serenity' : "v".concat(pimVersion === null || pimVersion === void 0 ? void 0 : pimVersion.pim_version.split('.')[0]);
        var campaign = isSerenity ? 'serenity' : "".concat(pimVersion === null || pimVersion === void 0 ? void 0 : pimVersion.pim_edition).concat(pimVersion === null || pimVersion === void 0 ? void 0 : pimVersion.pim_version);
        return "https://help.akeneo.com/pim/".concat(version, "/index.html?utm_source=akeneo-app&utm_medium=interrogation-icon&utm_campaign=").concat(campaign);
    }, [pimVersion]);
    return (React.createElement(React.Fragment, null,
        React.createElement(NavContainer, { "aria-label": "Main navigation" },
            React.createElement(MainNavContainer, null,
                React.createElement(LogoContainer, null,
                    React.createElement(PimView, { viewName: "pim-menu-logo" })),
                React.createElement(MenuContainer, null, entries.map(function (entry) { return (React.createElement(StyledMainNavigationItem, { id: entry.code, key: entry.code, active: entry.code === activeEntryCode, disabled: entry.disabled, icon: entry.icon, onClick: function (event) { return handleFollowEntry(event, entry); }, href: "#".concat(router.generate(entry.route)), role: "menuitem", "data-testid": "pim-main-menu-item", className: entry.code === activeEntryCode ? 'active' : undefined, align: entry.align, freeTrialEnabled: freeTrialEnabled },
                    translate(entry.title),
                    entry.disabled && freeTrialEnabled && (React.createElement(LockIconContainer, { "data-testid": "locked-entry" },
                        React.createElement(StyledTag, { tint: "blue" },
                            React.createElement(StyledLockIcon, { size: 16, color: theme.color.blue100 })))))); })),
                React.createElement(HelpContainer, { onMouseOver: function () { return setShowHelpDropdown(true); }, onMouseLeave: function () { return setShowHelpDropdown(false); } },
                    React.createElement(MainNavigationItem, { icon: React.createElement(HelpIcon, null) },
                        translate('pim_menu.tab.help.title'),
                        React.createElement(Tag, { tint: "blue" }, translate('pim_menu.tab.help.new'))))),
            activeNavigationEntry &&
                (!activeNavigationEntry.isLandingSectionPage || activeSubEntryCode) &&
                activeSubNavigation &&
                activeSubNavigation.sections.length > 0 && (React.createElement(SubNavigation, { entries: activeSubNavigation.entries, sections: activeSubNavigation.sections, backLink: activeSubNavigation.backLink, stateCode: activeSubNavigation.stateCode, title: activeSubNavigation.title, activeSubEntryCode: activeSubEntryCode, freeTrialEnabled: freeTrialEnabled }))),
        React.createElement(HelpMenuContainer, { show: showHelpDropdown },
            React.createElement("a", { href: helpCenterUrl, target: "_blank", title: translate('pim_menu.tab.help.helper') }, translate('pim_menu.tab.help.help_center')),
            React.createElement(LinkContainer, { href: "https://akademy.akeneo.com/", target: "_blank" },
                translate('pim_menu.tab.help.akademy_training'),
                React.createElement(Tag, { tint: 'blue' }, translate('pim_menu.tab.help.new'))),
            React.createElement("a", { href: "https://help.akeneo.com/pim/serenity/updates/index.html", target: "_blank" }, translate('pim_menu.tab.help.news')))));
};
var StyledTag = styled(Tag)(templateObject_4 || (templateObject_4 = __makeTemplateObject(["\n  padding: 0;\n  height: 24px;\n  width: 24px;\n"], ["\n  padding: 0;\n  height: 24px;\n  width: 24px;\n"])));
var StyledLockIcon = styled(LockIcon)(templateObject_5 || (templateObject_5 = __makeTemplateObject(["\n  margin: 3px;\n"], ["\n  margin: 3px;\n"])));
var LockIconContainer = styled.div(templateObject_6 || (templateObject_6 = __makeTemplateObject(["\n  position: absolute;\n  top: 0;\n  right: 12px;\n  width: 24px;\n  height: 24px;\n  display: flex;\n  align-items: center;\n  justify-content: center;\n"], ["\n  position: absolute;\n  top: 0;\n  right: 12px;\n  width: 24px;\n  height: 24px;\n  display: flex;\n  align-items: center;\n  justify-content: center;\n"])));
var NavContainer = styled.nav(templateObject_7 || (templateObject_7 = __makeTemplateObject(["\n  display: flex;\n  height: 100%;\n"], ["\n  display: flex;\n  height: 100%;\n"])));
var MainNavContainer = styled.div(templateObject_8 || (templateObject_8 = __makeTemplateObject(["\n  display: flex;\n  width: 100%;\n  flex-direction: column;\n  justify-content: start;\n  height: 100%;\n  border-right: 1px solid ", ";\n  z-index: 803;\n  background: white;\n  overflow: auto;\n"], ["\n  display: flex;\n  width: 100%;\n  flex-direction: column;\n  justify-content: start;\n  height: 100%;\n  border-right: 1px solid ", ";\n  z-index: 803;\n  background: white;\n  overflow: auto;\n"])), function (_a) {
    var theme = _a.theme;
    return theme.color.grey80;
});
var LogoContainer = styled.div(templateObject_9 || (templateObject_9 = __makeTemplateObject(["\n  height: 80px;\n  min-height: 80px;\n  position: relative;\n"], ["\n  height: 80px;\n  min-height: 80px;\n  position: relative;\n"])));
var MenuContainer = styled.div(templateObject_10 || (templateObject_10 = __makeTemplateObject(["\n  position: relative;\n  height: 100%;\n"], ["\n  position: relative;\n  height: 100%;\n"])));
var HelpMenuContainer = styled.div(templateObject_11 || (templateObject_11 = __makeTemplateObject(["\n  background-color: white;\n  display: ", ";\n  box-shadow: 0px 8px 16px 0px ", ";\n  z-index: 10000000; // huge z-index due to crips-client used in tria that has z-index 1000000\n  position: fixed;\n  left: 80px;\n  bottom: 10px;\n  flex-direction: column;\n\n  :hover {\n    display: flex;\n  }\n\n  a {\n    color: ", ";\n    padding: 12px 16px;\n\n    :hover {\n      color: ", ";\n    }\n\n    .AknBadge {\n      margin-left: 10px;\n    }\n  }\n\n  ", " {\n    margin-left: 10px;\n  }\n"], ["\n  background-color: white;\n  display: ", ";\n  box-shadow: 0px 8px 16px 0px ", ";\n  z-index: 10000000; // huge z-index due to crips-client used in tria that has z-index 1000000\n  position: fixed;\n  left: 80px;\n  bottom: 10px;\n  flex-direction: column;\n\n  :hover {\n    display: flex;\n  }\n\n  a {\n    color: ", ";\n    padding: 12px 16px;\n\n    :hover {\n      color: ", ";\n    }\n\n    .AknBadge {\n      margin-left: 10px;\n    }\n  }\n\n  ", " {\n    margin-left: 10px;\n  }\n"])), function (_a) {
    var show = _a.show;
    return (show ? 'flex' : 'none');
}, function (_a) {
    var theme = _a.theme;
    return theme.color.grey120;
}, function (_a) {
    var theme = _a.theme;
    return theme.color.grey120;
}, function (_a) {
    var theme = _a.theme;
    return theme.color.purple100;
}, Tag);
var HelpContainer = styled.div(templateObject_12 || (templateObject_12 = __makeTemplateObject(["\n  height: 80px;\n  min-height: 80px;\n  position: relative;\n  margin-top: auto;\n  display: inline-block;\n"], ["\n  height: 80px;\n  min-height: 80px;\n  position: relative;\n  margin-top: auto;\n  display: inline-block;\n"])));
var LinkContainer = styled.a(templateObject_13 || (templateObject_13 = __makeTemplateObject(["\n  display: flex;\n  align-items: center;\n"], ["\n  display: flex;\n  align-items: center;\n"])));
export { PimNavigation };
var templateObject_1, templateObject_2, templateObject_3, templateObject_4, templateObject_5, templateObject_6, templateObject_7, templateObject_8, templateObject_9, templateObject_10, templateObject_11, templateObject_12, templateObject_13;
//# sourceMappingURL=PimNavigation.js.map