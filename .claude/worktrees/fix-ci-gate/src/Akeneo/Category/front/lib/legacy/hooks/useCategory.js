"use strict";
var __read = (this && this.__read) || function (o, n) {
    var m = typeof Symbol === "function" && o[Symbol.iterator];
    if (!m) return o;
    var i = m.call(o), r, ar = [], e;
    try {
        while ((n === void 0 || n-- > 0) && !(r = i.next()).done) ar.push(r.value);
    }
    catch (error) { e = { error: error }; }
    finally {
        try {
            if (r && !r.done && (m = i["return"])) m.call(i);
        }
        finally { if (e) throw e.error; }
    }
    return ar;
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.useCategory = void 0;
var shared_1 = require("@akeneo-pim-community/shared");
var useCategory = function (categoryId) {
    var url = (0, shared_1.useRoute)('pim_enrich_categorytree_edit', {
        id: categoryId.toString(),
    });
    var _a = __read((0, shared_1.useFetch)(url), 4), categoryData = _a[0], load = _a[1], status = _a[2], error = _a[3];
    return [categoryData, load, status, error];
};
exports.useCategory = useCategory;
//# sourceMappingURL=useCategory.js.map