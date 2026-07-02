import { useState } from 'react';
import { uuid } from '../shared';
var useId = function (prefix) {
    if (prefix === void 0) { prefix = ''; }
    var id = useState("".concat(prefix).concat(uuid()))[0];
    return id;
};
export { useId };
//# sourceMappingURL=useId.js.map