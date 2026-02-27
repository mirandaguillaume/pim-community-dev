"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.HistoryPimView = void 0;
var jsx_runtime_1 = require("react/jsx-runtime");
var react_1 = require("react");
var shared_1 = require("@akeneo-pim-community/shared");
var HistoryPimView = function (_a) {
    var viewName = _a.viewName, className = _a.className, onBuild = _a.onBuild, _b = _a.version, version = _b === void 0 ? 0 : _b;
    var el = (0, react_1.useRef)(null);
    var viewRef = (0, react_1.useRef)(null);
    var viewBuilder = (0, shared_1.useViewBuilder)();
    var isMounted = (0, shared_1.useIsMounted)();
    (0, react_1.useEffect)(function () {
        if (!viewBuilder) {
            return;
        }
        viewBuilder
            .build(viewName)
            .then(function (view) {
            if (typeof onBuild === 'function') {
                return onBuild(view);
            }
            return view;
        })
            .then(function (view) {
            if (view !== null) {
                viewRef.current = view;
                view.setElement(el.current).render();
            }
        });
    }, [viewBuilder, viewName, isMounted, onBuild, version]);
    (0, react_1.useEffect)(function () { return function () {
        var _a;
        (_a = viewRef.current) === null || _a === void 0 ? void 0 : _a.remove();
    }; }, []);
    return ((0, jsx_runtime_1.jsx)("div", { children: (0, jsx_runtime_1.jsx)("div", { ref: el, className: className }, void 0) }, void 0));
};
exports.HistoryPimView = HistoryPimView;
//# sourceMappingURL=HistoryPimView.js.map