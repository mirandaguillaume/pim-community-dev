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
exports.useCategoryTreeDeprecated = void 0;
var react_1 = require("react");
var shared_1 = require("@akeneo-pim-community/shared");
var models_1 = require("../models");
var useCategoryTreeDeprecated = function (treeId, lastSelectedCategoryId) {
    var _a = __read((0, react_1.useState)(null), 2), tree = _a[0], setTree = _a[1];
    var _b = __read((0, react_1.useState)('idle'), 2), loadingStatus = _b[0], setLoadingStatus = _b[1];
    var _c = __read((0, react_1.useState)(null), 2), error = _c[0], setError = _c[1];
    var url = (0, shared_1.useRoute)('pim_enrich_categorytree_children', {
        _format: 'json',
        id: treeId.toString(),
        select_node_id: lastSelectedCategoryId,
        with_items_count: '0',
        include_parent: '1',
        include_sub: '0',
        context: 'manage',
    });
    var _d = __read((0, shared_1.useFetch)(url), 4), data = _d[0], fetch = _d[1], fetchStatus = _d[2], fetchError = _d[3];
    (0, react_1.useEffect)(function () {
        if (fetchStatus === 'fetching' || fetchStatus === 'idle') {
            return;
        }
        if (fetchStatus === 'error') {
            setLoadingStatus(fetchStatus);
            setError(fetchError);
            setTree(null);
            return;
        }
        if (data === null) {
            setTree(null);
            setLoadingStatus('error');
            setError("Category tree [" + treeId + "] not found");
            return;
        }
        var tree = (0, models_1.convertToCategoryTree)(data);
        if (!tree.isRoot) {
            setTree(null);
            setLoadingStatus('error');
            setError("Category tree [" + treeId + "] not found");
            return;
        }
        setTree(tree);
        setLoadingStatus(fetchStatus);
        setError(fetchError);
    }, [data, fetchError, fetchStatus, treeId]);
    return {
        tree: tree,
        loadTree: fetch,
        loadingStatus: loadingStatus,
        error: error,
    };
};
exports.useCategoryTreeDeprecated = useCategoryTreeDeprecated;
//# sourceMappingURL=useCategoryTreeDeprecated.js.map