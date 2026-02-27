"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.isDefaultJobExecutionFilter = exports.getDefaultJobExecutionFilter = void 0;
var ITEMS_PER_PAGE = 25;
var getDefaultJobExecutionFilter = function () { return ({
    page: 1,
    size: ITEMS_PER_PAGE,
    sort: { column: 'started_at', direction: 'DESC' },
    automation: null,
    type: [],
    status: [],
    user: [],
    search: '',
    code: [],
}); };
exports.getDefaultJobExecutionFilter = getDefaultJobExecutionFilter;
var isDefaultJobExecutionFilter = function (_a) {
    var page = _a.page, size = _a.size, type = _a.type, status = _a.status, search = _a.search, user = _a.user, code = _a.code;
    return 1 === page &&
        ITEMS_PER_PAGE === size &&
        0 === status.length &&
        0 === type.length &&
        0 === code.length &&
        0 === user.length &&
        '' === search;
};
exports.isDefaultJobExecutionFilter = isDefaultJobExecutionFilter;
//# sourceMappingURL=JobExecutionFilter.js.map