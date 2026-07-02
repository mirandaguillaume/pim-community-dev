import { useState } from 'react';
import { arrayUnique } from '../shared';
var useProgress = function (steps) {
    if (0 === steps.length) {
        throw new Error('Steps array cannot be empty');
    }
    if (arrayUnique(steps).length !== steps.length) {
        throw new Error('Steps array cannot have duplicated names');
    }
    var _a = useState(0), current = _a[0], setCurrent = _a[1];
    var isCurrent = function (step) { return steps.indexOf(step) === current; };
    var next = function () { return setCurrent(function (current) { return (current === steps.length - 1 ? current : current + 1); }); };
    var previous = function () { return setCurrent(function (current) { return (current === 0 ? current : current - 1); }); };
    return [isCurrent, next, previous];
};
export { useProgress };
//# sourceMappingURL=useProgress.js.map