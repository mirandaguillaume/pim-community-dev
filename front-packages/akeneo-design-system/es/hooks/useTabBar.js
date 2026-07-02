import { useCallback, useState } from 'react';
var useTabBar = function (defaultTab) {
    var _a = useState(defaultTab), current = _a[0], setCurrent = _a[1];
    var isCurrent = useCallback(function (tab) { return tab === current; }, [current]);
    var switchTo = useCallback(function (tab) { return setCurrent(tab); }, []);
    return [isCurrent, switchTo, current];
};
export { useTabBar };
//# sourceMappingURL=useTabBar.js.map