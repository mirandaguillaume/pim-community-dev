import React, { cloneElement, isValidElement } from 'react';
import { Dropdown, IconButton, MoreIcon, useBooleanState } from 'akeneo-design-system';
import { useTranslate } from '../hooks';
var SecondaryActions = function (_a) {
    var children = _a.children;
    var translate = useTranslate();
    var _b = useBooleanState(), isOpen = _b[0], open = _b[1], close = _b[2];
    var items = React.Children.map(children, function (child) {
        return isValidElement(child) &&
            cloneElement(child, {
                onClick: function () {
                    var _a, _b;
                    close();
                    (_b = (_a = child.props).onClick) === null || _b === void 0 ? void 0 : _b.call(_a);
                },
            });
    });
    return (React.createElement(Dropdown, null,
        React.createElement(IconButton, { icon: React.createElement(MoreIcon, null), ghost: "borderless", level: "tertiary", title: translate('pim_common.other_actions'), onClick: open }),
        isOpen && (React.createElement(Dropdown.Overlay, { verticalPosition: "down", onClose: close },
            React.createElement(Dropdown.Header, null,
                React.createElement(Dropdown.Title, null, translate('pim_common.other_actions'))),
            React.createElement(Dropdown.ItemCollection, null, items)))));
};
SecondaryActions.Item = Dropdown.Item;
export { SecondaryActions };
//# sourceMappingURL=SecondaryActions.js.map