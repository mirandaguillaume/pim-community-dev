import { useState } from 'react';
var useSelection = function () {
    var _a = useState(false), checked = _a[0], setChecked = _a[1];
    return { checked: checked, onChange: function () { return setChecked(!checked); } };
};
export { useSelection };
//# sourceMappingURL=useSelection.js.map