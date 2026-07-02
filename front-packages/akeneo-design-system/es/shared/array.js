var __spreadArray = (this && this.__spreadArray) || function (to, from, pack) {
    if (pack || arguments.length === 2) for (var i = 0, l = from.length, ar; i < l; i++) {
        if (ar || !(i in from)) {
            if (!ar) ar = Array.prototype.slice.call(from, 0, i);
            ar[i] = from[i];
        }
    }
    return to.concat(ar || Array.prototype.slice.call(from));
};
var arrayUnique = function (arrayWithDuplicatedItems, comparator) {
    if (undefined === comparator)
        return Array.from(new Set(arrayWithDuplicatedItems));
    return arrayWithDuplicatedItems.reduce(function (uniqueItems, current) {
        if (uniqueItems.some(function (item) { return comparator(item, current); })) {
            return uniqueItems;
        }
        return __spreadArray(__spreadArray([], uniqueItems, true), [current], false);
    }, []);
};
export { arrayUnique };
//# sourceMappingURL=array.js.map