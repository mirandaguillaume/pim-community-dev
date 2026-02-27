"use strict";
var __awaiter = (this && this.__awaiter) || function (thisArg, _arguments, P, generator) {
    function adopt(value) { return value instanceof P ? value : new P(function (resolve) { resolve(value); }); }
    return new (P || (P = Promise))(function (resolve, reject) {
        function fulfilled(value) { try { step(generator.next(value)); } catch (e) { reject(e); } }
        function rejected(value) { try { step(generator["throw"](value)); } catch (e) { reject(e); } }
        function step(result) { result.done ? resolve(result.value) : adopt(result.value).then(fulfilled, rejected); }
        step((generator = generator.apply(thisArg, _arguments || [])).next());
    });
};
var __generator = (this && this.__generator) || function (thisArg, body) {
    var _ = { label: 0, sent: function() { if (t[0] & 1) throw t[1]; return t[1]; }, trys: [], ops: [] }, f, y, t, g;
    return g = { next: verb(0), "throw": verb(1), "return": verb(2) }, typeof Symbol === "function" && (g[Symbol.iterator] = function() { return this; }), g;
    function verb(n) { return function (v) { return step([n, v]); }; }
    function step(op) {
        if (f) throw new TypeError("Generator is already executing.");
        while (_) try {
            if (f = 1, y && (t = op[0] & 2 ? y["return"] : op[0] ? y["throw"] || ((t = y["return"]) && t.call(y), 0) : y.next) && !(t = t.call(y, op[1])).done) return t;
            if (y = 0, t) op = [op[0] & 2, t.value];
            switch (op[0]) {
                case 0: case 1: t = op; break;
                case 4: _.label++; return { value: op[1], done: false };
                case 5: _.label++; y = op[1]; op = [0]; continue;
                case 7: op = _.ops.pop(); _.trys.pop(); continue;
                default:
                    if (!(t = _.trys, t = t.length > 0 && t[t.length - 1]) && (op[0] === 6 || op[0] === 2)) { _ = 0; continue; }
                    if (op[0] === 3 && (!t || (op[1] > t[0] && op[1] < t[3]))) { _.label = op[1]; break; }
                    if (op[0] === 6 && _.label < t[1]) { _.label = t[1]; t = op; break; }
                    if (t && _.label < t[2]) { _.label = t[2]; _.ops.push(op); break; }
                    if (t[2]) _.ops.pop();
                    _.trys.pop(); continue;
            }
            op = body.call(thisArg, _);
        } catch (e) { op = [6, e]; y = 0; } finally { f = t = 0; }
        if (op[0] & 5) throw op[1]; return { value: op[0] ? op[1] : void 0, done: true };
    }
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.useJobExecutionTable = void 0;
var react_1 = require("react");
var shared_1 = require("@akeneo-pim-community/shared");
var AUTO_REFRESH_FREQUENCY = 5000;
var useJobExecutionTable = function (_a, autoRefresh) {
    var page = _a.page, size = _a.size, sort = _a.sort, automation = _a.automation, type = _a.type, status = _a.status, code = _a.code, user = _a.user, search = _a.search;
    if (autoRefresh === void 0) { autoRefresh = true; }
    var _b = (0, react_1.useState)(null), jobExecutionTable = _b[0], setJobExecutionTable = _b[1];
    var route = (0, shared_1.useRoute)('akeneo_job_index_action');
    var isMounted = (0, shared_1.useIsMounted)();
    var isDocumentVisible = (0, shared_1.useDocumentVisibility)();
    var isFetching = (0, react_1.useRef)(false);
    var searchJobExecution = (0, react_1.useCallback)(function () { return __awaiter(void 0, void 0, void 0, function () {
        var response, jobExecutionTable;
        return __generator(this, function (_a) {
            switch (_a.label) {
                case 0:
                    if (isFetching.current)
                        return [2];
                    isFetching.current = true;
                    return [4, fetch(route, {
                            body: JSON.stringify({
                                page: page.toString(),
                                size: size.toString(),
                                sort: sort,
                                status: status,
                                automation: automation,
                                type: type,
                                user: user,
                                search: search,
                                code: code,
                            }),
                            headers: {
                                'Content-Type': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            method: 'POST',
                        })];
                case 1:
                    response = _a.sent();
                    isFetching.current = false;
                    return [4, response.json()];
                case 2:
                    jobExecutionTable = _a.sent();
                    if (isMounted()) {
                        setJobExecutionTable(jobExecutionTable);
                    }
                    return [2];
            }
        });
    }); }, [isMounted, route, page, size, sort, automation, type, status, search, user, code]);
    (0, react_1.useEffect)(function () {
        void searchJobExecution();
    }, [searchJobExecution]);
    (0, react_1.useEffect)(function () {
        if (!isDocumentVisible || !autoRefresh)
            return;
        var interval = setInterval(function () {
            void searchJobExecution();
        }, AUTO_REFRESH_FREQUENCY);
        return function () {
            clearInterval(interval);
        };
    }, [isDocumentVisible, searchJobExecution, page, size, sort, type, status, search, user, code, autoRefresh]);
    return [jobExecutionTable, searchJobExecution];
};
exports.useJobExecutionTable = useJobExecutionTable;
//# sourceMappingURL=useJobExecutionTable.js.map