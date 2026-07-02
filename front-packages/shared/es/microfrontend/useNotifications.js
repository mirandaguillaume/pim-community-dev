var __spreadArray = (this && this.__spreadArray) || function (to, from, pack) {
    if (pack || arguments.length === 2) for (var i = 0, l = from.length, ar; i < l; i++) {
        if (ar || !(i in from)) {
            if (!ar) ar = Array.prototype.slice.call(from, 0, i);
            ar[i] = from[i];
        }
    }
    return to.concat(ar || Array.prototype.slice.call(from));
};
import { useCallback, useState } from 'react';
import { uuid } from 'akeneo-design-system';
var useNotifications = function () {
    var _a = useState([]), notifications = _a[0], setNotifications = _a[1];
    var notify = useCallback(function (level, title, children, icon) {
        setNotifications(function (notifications) { return __spreadArray(__spreadArray([], notifications, true), [{ identifier: uuid(), level: level, title: title, children: children, icon: icon }], false); });
    }, []);
    var handleNotificationClose = useCallback(function (identifier) {
        setNotifications(function (notifications) { return notifications.filter(function (notification) { return notification.identifier !== identifier; }); });
    }, []);
    return [notifications, notify, handleNotificationClose];
};
export { useNotifications };
//# sourceMappingURL=useNotifications.js.map