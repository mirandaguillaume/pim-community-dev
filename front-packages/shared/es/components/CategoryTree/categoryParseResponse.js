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
var parseResponse = function (json, options) {
    var _a = __assign({ readOnly: false, lockedCategoryIds: [], isRoot: false, selectable: false }, options), readOnly = _a.readOnly, lockedCategoryIds = _a.lockedCategoryIds, isRoot = _a.isRoot, selectable = _a.selectable;
    var getChildren = function () {
        if (json.state.includes('closed')) {
            return undefined;
        }
        if (json.state.includes('leaf')) {
            return [];
        }
        if (json.children) {
            return json.children.map(function (child) { return parseResponse(child, { readOnly: readOnly, lockedCategoryIds: lockedCategoryIds, isRoot: false, selectable: selectable }); });
        }
        return undefined;
    };
    var categoryId = Number(json.attr.id.replace(/^node_(\d+)$/, '$1'));
    return {
        id: categoryId,
        code: json.attr['data-code'],
        label: json.data,
        children: getChildren(),
        selected: json.state.includes('jstree-checked'),
        readOnly: readOnly || lockedCategoryIds.indexOf(categoryId) >= 0,
        selectable: !isRoot && selectable,
    };
};
export { parseResponse };
//# sourceMappingURL=categoryParseResponse.js.map