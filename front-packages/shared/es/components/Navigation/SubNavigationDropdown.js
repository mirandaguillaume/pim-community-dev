import { Dropdown, IconButton, MoreVerticalIcon, useBooleanState } from 'akeneo-design-system';
import React from 'react';
import { useRouter, useTranslate } from '../../hooks';
var SubNavigationDropdown = function (_a) {
    var entries = _a.entries, title = _a.title;
    var translate = useTranslate();
    var router = useRouter();
    var _b = useBooleanState(false), isMenuOpen = _b[0], openMenu = _b[1], closeMenu = _b[2];
    var handleFollowSubEntry = function (subEntry) {
        closeMenu();
        router.redirect(router.generate(subEntry.route, subEntry.routeParams));
    };
    return (React.createElement(Dropdown, null,
        React.createElement(IconButton, { level: "tertiary", title: title ? translate(title) : '', icon: React.createElement(MoreVerticalIcon, null), ghost: "borderless", onClick: openMenu, className: "dropdown-button", "data-testid": 'openSubNavigationDropdownButton' }),
        isMenuOpen && (React.createElement(Dropdown.Overlay, { onClose: closeMenu },
            title && (React.createElement(Dropdown.Header, null,
                React.createElement(Dropdown.Title, null, translate(title)))),
            React.createElement(Dropdown.ItemCollection, null, entries.map(function (subEntry) { return (React.createElement(Dropdown.Item, { onClick: function () { return handleFollowSubEntry(subEntry); }, key: subEntry.code }, subEntry.title)); }))))));
};
export { SubNavigationDropdown };
//# sourceMappingURL=SubNavigationDropdown.js.map