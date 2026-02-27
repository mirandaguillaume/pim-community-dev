"use strict";
var __assign = (this && this.__assign) || function () {
    __assign = Object.assign || function(t) {
        for (var s, i = 1, n = arguments.length; i < n; i++) {
            s = arguments[i];
            for (var p in s) if (Object.prototype.hasOwnProperty.call(s, p))
                t[p] = s[p];
        }
        return t;
    };
    return __assign.apply(this, arguments);
};
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
exports.useCountProductsBeforeDeleteCategory = void 0;
var useCountProductsByCategory_1 = require("./useCountProductsByCategory");
var react_1 = require("react");
var useCountProductsBeforeDeleteCategory = function (categoryId) {
    var _a = __read((0, react_1.useState)(null), 2), categoryDeletion = _a[0], setCategoryDeletion = _a[1];
    var _b = (0, useCountProductsByCategory_1.useCountProductsByCategory)(categoryId), numberOfProducts = _b.numberOfProducts, loadNumberOfProducts = _b.loadNumberOfProducts;
    var beforeDelete = function (deleteCategory) {
        setCategoryDeletion({ callback: deleteCategory, status: numberOfProducts !== null ? 'ready' : 'pending' });
    };
    (0, react_1.useEffect)(function () {
        if (categoryDeletion === null) {
            return;
        }
        if (categoryDeletion.status === 'pending') {
            loadNumberOfProducts();
            return;
        }
        if (categoryDeletion.status === 'ready' && numberOfProducts !== null) {
            categoryDeletion.callback(numberOfProducts);
            setCategoryDeletion(null);
        }
    }, [categoryDeletion]);
    (0, react_1.useEffect)(function () {
        if (categoryDeletion !== null && numberOfProducts !== null) {
            setCategoryDeletion(__assign(__assign({}, categoryDeletion), { status: 'ready' }));
        }
    }, [numberOfProducts]);
    return beforeDelete;
};
exports.useCountProductsBeforeDeleteCategory = useCountProductsBeforeDeleteCategory;
//# sourceMappingURL=useCountProductsBeforeDeleteCategory.js.map