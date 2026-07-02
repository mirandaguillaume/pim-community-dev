import { useEffect } from 'react';
var useSetPageTitle = function (title) {
    useEffect(function () {
        document.title = title;
    }, [title]);
};
export { useSetPageTitle };
//# sourceMappingURL=useSetPageTitle.js.map