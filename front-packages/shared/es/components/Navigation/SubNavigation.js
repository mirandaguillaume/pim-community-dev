var __makeTemplateObject = (this && this.__makeTemplateObject) || function (cooked, raw) {
    if (Object.defineProperty) { Object.defineProperty(cooked, "raw", { value: raw }); } else { cooked.raw = raw; }
    return cooked;
};
import React, { useEffect } from 'react';
import styled, { css } from 'styled-components';
import { Badge, LockIcon, SubNavigationItem, SubNavigationPanel, Tag, useBooleanState, } from 'akeneo-design-system';
import { useRouter, useTranslate } from '../../hooks';
import { SubNavigationDropdown } from './SubNavigationDropdown';
import { useTheme } from 'akeneo-design-system';
var SubNavigation = function (_a) {
    var title = _a.title, sections = _a.sections, entries = _a.entries, backLink = _a.backLink, stateCode = _a.stateCode, activeSubEntryCode = _a.activeSubEntryCode, freeTrialEnabled = _a.freeTrialEnabled;
    var translate = useTranslate();
    var router = useRouter();
    var subNavigationState = sessionStorage.getItem("collapsedColumn_".concat(stateCode));
    var _b = useBooleanState(subNavigationState === null || subNavigationState === '1'), isSubNavigationOpened = _b[0], openSubNavigation = _b[1], closeSubNavigation = _b[2];
    useEffect(function () {
        sessionStorage.setItem("collapsedColumn_".concat(stateCode), isSubNavigationOpened ? '1' : '0');
    }, [isSubNavigationOpened]);
    var handleFollowSubEntry = function (event, subEntry) {
        event.stopPropagation();
        event.preventDefault();
        router.redirect(router.generate(subEntry.route, subEntry.routeParams));
    };
    var theme = useTheme();
    return (React.createElement(SubNavContainer, { role: "menu", "data-testid": "pim-sub-menu" },
        React.createElement(SubNavigationPanel, { isOpen: isSubNavigationOpened, open: openSubNavigation, close: closeSubNavigation, closeTitle: translate('pim_common.close'), openTitle: translate('pim_common.open') },
            React.createElement(SubNavigationPanel.Collapsed, null,
                React.createElement(SubNavigationDropdown, { entries: entries, title: title })),
            backLink && (React.createElement(Backlink, { onClick: function () { return router.redirectToRoute(backLink.route); } }, translate(backLink.title))),
            sections.map(function (section) {
                return (React.createElement(Section, { key: section.code },
                    React.createElement(SectionTitle, null, translate(section.title)),
                    entries
                        .filter(function (subNav) { return subNav.sectionCode === section.code; })
                        .map(function (subEntry) { return (React.createElement(StyledSubNavigationItem, { id: subEntry.code, active: subEntry.code === activeSubEntryCode, key: subEntry.code, href: subEntry.disabled ? undefined : "#".concat(router.generate(subEntry.route, subEntry.routeParams)), onClick: function (event) { return handleFollowSubEntry(event, subEntry); }, role: "menuitem", disabled: subEntry.disabled, hasIconTag: subEntry.disabled && freeTrialEnabled },
                        subEntry.title,
                        subEntry.disabled && freeTrialEnabled && (React.createElement(Tag, { tint: "blue" },
                            React.createElement(StyledLockIcon, { size: 16, color: theme.color.blue100 }))),
                        subEntry.new && React.createElement(StyledBadge, { level: "secondary" }, translate('pim_menu.tag.new')))); })));
            }),
            React.createElement("div", { className: "subnavigation-additional-container" }))));
};
var SubNavContainer = styled.div(templateObject_1 || (templateObject_1 = __makeTemplateObject([""], [""])));
var SectionTitle = styled.div(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n  margin-bottom: 20px;\n  color: ", ";\n  text-transform: uppercase;\n  font-size: 11px;\n  line-height: 20px;\n"], ["\n  margin-bottom: 20px;\n  color: ", ";\n  text-transform: uppercase;\n  font-size: 11px;\n  line-height: 20px;\n"])), function (_a) {
    var theme = _a.theme;
    return theme.color.grey100;
});
var Section = styled.div(templateObject_3 || (templateObject_3 = __makeTemplateObject(["\n  :not(:first-child) {\n    margin-top: 30px;\n  }\n"], ["\n  :not(:first-child) {\n    margin-top: 30px;\n  }\n"])));
var Backlink = styled.div(templateObject_4 || (templateObject_4 = __makeTemplateObject(["\n  font-size: ", ";\n  color: ", ";\n  cursor: pointer;\n  padding-bottom: 10px;\n"], ["\n  font-size: ", ";\n  color: ", ";\n  cursor: pointer;\n  padding-bottom: 10px;\n"])), function (_a) {
    var theme = _a.theme;
    return theme.fontSize.big;
}, function (_a) {
    var theme = _a.theme;
    return theme.color.grey140;
});
var StyledSubNavigationItem = styled(SubNavigationItem)(templateObject_7 || (templateObject_7 = __makeTemplateObject(["\n  ", " {\n    align-self: center;\n    box-sizing: content-box;\n\n    ", "\n\n  ", "\n"], ["\n  ", " {\n    align-self: center;\n    box-sizing: content-box;\n\n    ", "\n\n  ", "\n"])), Tag, function (_a) {
    var hasIconTag = _a.hasIconTag;
    return hasIconTag && css(templateObject_5 || (templateObject_5 = __makeTemplateObject(["\n        height: 24px;\n        padding: 0;\n        box-sizing: border-box;\n      "], ["\n        height: 24px;\n        padding: 0;\n        box-sizing: border-box;\n      "])));
}, function (_a) {
    var disabled = _a.disabled;
    return disabled && css(templateObject_6 || (templateObject_6 = __makeTemplateObject(["\n      cursor: pointer;\n    "], ["\n      cursor: pointer;\n    "])));
});
var StyledLockIcon = styled(LockIcon)(templateObject_8 || (templateObject_8 = __makeTemplateObject(["\n  margin: 3px;\n"], ["\n  margin: 3px;\n"])));
var StyledBadge = styled(Badge)(templateObject_9 || (templateObject_9 = __makeTemplateObject(["\n  margin-left: 10px;\n  vertical-align: text-bottom;\n"], ["\n  margin-left: 10px;\n  vertical-align: text-bottom;\n"])));
export { SubNavigation };
var templateObject_1, templateObject_2, templateObject_3, templateObject_4, templateObject_5, templateObject_6, templateObject_7, templateObject_8, templateObject_9;
//# sourceMappingURL=SubNavigation.js.map