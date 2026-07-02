var __makeTemplateObject = (this && this.__makeTemplateObject) || function (cooked, raw) {
    if (Object.defineProperty) { Object.defineProperty(cooked, "raw", { value: raw }); } else { cooked.raw = raw; }
    return cooked;
};
import React, { Children, cloneElement, isValidElement } from 'react';
import styled from 'styled-components';
import { Actions, Breadcrumb, Illustration, State, Title, UserActions, Content, AutoSaveStatus } from './header';
import { SandboxHelper } from './SandboxHelper';
import { useFeatureFlags, useSystemConfiguration } from '../../hooks';
var Header = styled.header(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  position: sticky;\n  top: ", "px;\n  padding: 40px 40px 20px;\n  background: white;\n  z-index: 10;\n"], ["\n  position: sticky;\n  top: ", "px;\n  padding: 40px 40px 20px;\n  background: white;\n  z-index: 10;\n"])), function (_a) {
    var top = _a.top;
    return top;
});
var LineContainer = styled.div(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n  display: flex;\n  flex-direction: row;\n  justify-content: space-between;\n"], ["\n  display: flex;\n  flex-direction: row;\n  justify-content: space-between;\n"])));
var MainContainer = styled.div(templateObject_3 || (templateObject_3 = __makeTemplateObject(["\n  flex-grow: 1;\n  display: flex;\n  justify-content: space-between;\n  flex-direction: column;\n  max-width: 100%;\n  width: 1px;\n\n  ", " {\n    min-height: 34px;\n  }\n"], ["\n  flex-grow: 1;\n  display: flex;\n  justify-content: space-between;\n  flex-direction: column;\n  max-width: 100%;\n  width: 1px;\n\n  ", " {\n    min-height: 34px;\n  }\n"])), LineContainer);
var ActionsContainer = styled.div(templateObject_4 || (templateObject_4 = __makeTemplateObject(["\n  display: flex;\n  align-content: baseline;\n  gap: 10px;\n"], ["\n  display: flex;\n  align-content: baseline;\n  gap: 10px;\n"])));
var buildHeaderElements = function (children, showPlaceholder) {
    var headerElements = {
        illustration: undefined,
        breadcrumb: undefined,
        autoSaveStatus: undefined,
        title: undefined,
        state: undefined,
        actions: undefined,
        userActions: undefined,
        content: undefined,
    };
    Children.forEach(children, function (child) {
        if (!isValidElement(child)) {
            return;
        }
        switch (child.type) {
            case Illustration:
                headerElements.illustration = child;
                break;
            case Breadcrumb:
                headerElements.breadcrumb = child;
                break;
            case Title:
                headerElements.title = React.cloneElement(child, {
                    showPlaceholder: showPlaceholder,
                });
                break;
            case State:
                headerElements.state = child;
                break;
            case Actions:
                headerElements.actions = child;
                break;
            case UserActions:
                headerElements.userActions = child;
                break;
            case Content:
                headerElements.content = child;
                break;
            case AutoSaveStatus:
                headerElements.autoSaveStatus = child;
                break;
        }
    });
    if (headerElements.userActions !== undefined && headerElements.actions !== undefined) {
        headerElements.actions = cloneElement(headerElements.actions, {
            userActionVisible: true,
        });
    }
    return headerElements;
};
var PageHeader = function (_a) {
    var children = _a.children, showPlaceholder = _a.showPlaceholder;
    var _b = buildHeaderElements(children, showPlaceholder), illustration = _b.illustration, breadcrumb = _b.breadcrumb, title = _b.title, state = _b.state, actions = _b.actions, userActions = _b.userActions, content = _b.content, autoSaveStatus = _b.autoSaveStatus;
    var isEnabled = useFeatureFlags().isEnabled;
    var isSandboxBannerDisplayed = isEnabled('sandbox_banner') && useSystemConfiguration().get('sandbox_banner');
    return (React.createElement(React.Fragment, null,
        React.createElement(SandboxHelper, null),
        React.createElement(Header, { top: isSandboxBannerDisplayed ? 44 : 0 },
            React.createElement(LineContainer, null,
                illustration,
                React.createElement(MainContainer, null,
                    React.createElement("div", null,
                        React.createElement(LineContainer, null,
                            breadcrumb,
                            autoSaveStatus,
                            React.createElement(ActionsContainer, null,
                                userActions,
                                actions)),
                        React.createElement(LineContainer, null,
                            title,
                            state),
                        content))))));
};
PageHeader.Actions = Actions;
PageHeader.Breadcrumb = Breadcrumb;
PageHeader.Illustration = Illustration;
PageHeader.UserActions = UserActions;
PageHeader.Title = Title;
PageHeader.State = State;
PageHeader.Content = Content;
PageHeader.AutoSaveStatus = AutoSaveStatus;
export { PageHeader };
var templateObject_1, templateObject_2, templateObject_3, templateObject_4;
//# sourceMappingURL=PageHeader.js.map