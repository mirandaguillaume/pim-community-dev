"use strict";
var __makeTemplateObject = (this && this.__makeTemplateObject) || function (cooked, raw) {
    if (Object.defineProperty) { Object.defineProperty(cooked, "raw", { value: raw }); } else { cooked.raw = raw; }
    return cooked;
};
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
var __createBinding = (this && this.__createBinding) || (Object.create ? (function(o, m, k, k2) {
    if (k2 === undefined) k2 = k;
    Object.defineProperty(o, k2, { enumerable: true, get: function() { return m[k]; } });
}) : (function(o, m, k, k2) {
    if (k2 === undefined) k2 = k;
    o[k2] = m[k];
}));
var __setModuleDefault = (this && this.__setModuleDefault) || (Object.create ? (function(o, v) {
    Object.defineProperty(o, "default", { enumerable: true, value: v });
}) : function(o, v) {
    o["default"] = v;
});
var __importStar = (this && this.__importStar) || function (mod) {
    if (mod && mod.__esModule) return mod;
    var result = {};
    if (mod != null) for (var k in mod) if (k !== "default" && Object.prototype.hasOwnProperty.call(mod, k)) __createBinding(result, mod, k);
    __setModuleDefault(result, mod);
    return result;
};
var __rest = (this && this.__rest) || function (s, e) {
    var t = {};
    for (var p in s) if (Object.prototype.hasOwnProperty.call(s, p) && e.indexOf(p) < 0)
        t[p] = s[p];
    if (s != null && typeof Object.getOwnPropertySymbols === "function")
        for (var i = 0, p = Object.getOwnPropertySymbols(s); i < p.length; i++) {
            if (e.indexOf(p[i]) < 0 && Object.prototype.propertyIsEnumerable.call(s, p[i]))
                t[p[i]] = s[p[i]];
        }
    return t;
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
exports.Tree = void 0;
var jsx_runtime_1 = require("react/jsx-runtime");
var react_1 = __importStar(require("react"));
var styled_components_1 = __importStar(require("styled-components"));
var akeneo_design_system_1 = require("akeneo-design-system");
var TreeRow_1 = require("./TreeRow");
var TreeActions_1 = require("./TreeActions");
var TreeIcon_1 = require("./TreeIcon");
var TreeSkeleton_1 = require("./TreeSkeleton");
var placeholderPositionStyles = (0, styled_components_1.css)(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  &:after {\n    content: ' ';\n    position: absolute;\n    box-sizing: border-box;\n    z-index: 1;\n    left: 0;\n    right: 0;\n    padding: 0;\n    width: 100%;\n    height: 4px;\n    margin-top: -2px;\n    background: linear-gradient(to top, ", " 4px, ", " 0px);\n    pointer-events: none;\n  }\n"], ["\n  &:after {\n    content: ' ';\n    position: absolute;\n    box-sizing: border-box;\n    z-index: 1;\n    left: 0;\n    right: 0;\n    padding: 0;\n    width: 100%;\n    height: 4px;\n    margin-top: -2px;\n    background: linear-gradient(to top, ", " 4px, ", " 0px);\n    pointer-events: none;\n  }\n"])), (0, akeneo_design_system_1.getColor)('blue40'), (0, akeneo_design_system_1.getColor)('white'));
var TreeContainer = styled_components_1.default.li(templateObject_3 || (templateObject_3 = __makeTemplateObject(["\n  display: block;\n  color: ", ";\n\n  ", ";\n\n  ", "\n"], ["\n  display: block;\n  color: ", ";\n\n  ", ";\n\n  ", "\n"])), (0, akeneo_design_system_1.getColor)('grey140'), function (_a) {
    var isRoot = _a.isRoot;
    return isRoot && (0, styled_components_1.css)(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n      position: relative;\n    "], ["\n      position: relative;\n    "])));
}, function (_a) {
    var placeholderPosition = _a.placeholderPosition;
    return placeholderPosition && placeholderPositionStyles;
});
var SubTreesContainer = styled_components_1.default.ul(templateObject_4 || (templateObject_4 = __makeTemplateObject(["\n  margin: 0 0 0 20px;\n  padding: 0;\n"], ["\n  margin: 0 0 0 20px;\n  padding: 0;\n"])));
var Tree = function (_a) {
    var label = _a.label, value = _a.value, children = _a.children, _b = _a.isLeaf, isLeaf = _b === void 0 ? false : _b, _c = _a.selected, selected = _c === void 0 ? false : _c, _d = _a.isLoading, isLoading = _d === void 0 ? false : _d, _e = _a.readOnly, readOnly = _e === void 0 ? false : _e, _f = _a.isOpen, isOpen = _f === void 0 ? false : _f, open = _a.open, close = _a.close, onChange = _a.onChange, onClick = _a.onClick, onDrop = _a.onDrop, onDragStart = _a.onDragStart, onDragEnd = _a.onDragEnd, onDragOver = _a.onDragOver, onDragEnter = _a.onDragEnter, onDragLeave = _a.onDragLeave, _g = _a._isRoot, _isRoot = _g === void 0 ? true : _g, _h = _a.disabled, disabled = _h === void 0 ? false : _h, _j = _a.draggable, draggable = _j === void 0 ? false : _j, placeholderPosition = _a.placeholderPosition, rest = __rest(_a, ["label", "value", "children", "isLeaf", "selected", "isLoading", "readOnly", "isOpen", "open", "close", "onChange", "onClick", "onDrop", "onDragStart", "onDragEnd", "onDragOver", "onDragEnter", "onDragLeave", "_isRoot", "disabled", "draggable", "placeholderPosition"]);
    var subTrees = [];
    var actions = null;
    react_1.default.Children.forEach(children, function (child) {
        if ((0, react_1.isValidElement)(child) && child.type === TreeActions_1.TreeActions) {
            actions = child;
            return;
        }
        if (!(0, react_1.isValidElement)(child)) {
            return;
        }
        subTrees.push(child);
    });
    var dragRef = (0, react_1.useRef)(null);
    var treeRowRef = (0, react_1.useRef)(null);
    var _k = __read((0, react_1.useState)(null), 2), timer = _k[0], setTimer = _k[1];
    var _l = __read((0, react_1.useState)(false), 2), ticking = _l[0], setTicking = _l[1];
    var handleArrowClick = (0, react_1.useCallback)(function (event) {
        event.stopPropagation();
        if (isLeaf) {
            return;
        }
        isOpen ? close && close() : open && open();
    }, [isOpen, close, open, isLeaf]);
    var handleClick = (0, react_1.useCallback)(function (event) {
        if (onClick) {
            onClick(value);
        }
        else {
            handleArrowClick(event);
        }
    }, [handleArrowClick, onClick, value]);
    var result = ((0, jsx_runtime_1.jsxs)(TreeContainer, __assign({ role: "treeitem", "aria-expanded": isOpen, isRoot: _isRoot, placeholderPosition: isOpen && placeholderPosition === 'bottom' ? placeholderPosition : undefined }, rest, { children: [(0, jsx_runtime_1.jsxs)(TreeRow_1.TreeRow, __assign({ ref: treeRowRef, onClick: handleClick, "$selected": selected, "$disabled": disabled, isRoot: _isRoot, draggable: draggable, placeholderPosition: isOpen && placeholderPosition === 'bottom' ? undefined : placeholderPosition, onDragStartCapture: function (event) {
                    if (event.target !== dragRef.current) {
                        event.preventDefault();
                    }
                }, onDragOver: function (event) {
                    event.stopPropagation();
                    event.preventDefault();
                    var cursorPosition = {
                        x: event.clientX,
                        y: event.clientY,
                    };
                    if (!ticking) {
                        requestAnimationFrame(function () {
                            if (onDragOver && treeRowRef.current) {
                                onDragOver(treeRowRef.current, cursorPosition);
                            }
                            setTicking(false);
                        });
                    }
                    setTicking(true);
                }, onDragEnter: function () {
                    if (onDragEnter) {
                        onDragEnter();
                    }
                    if (!isLeaf && !disabled && open) {
                        if (timer) {
                            return;
                        }
                        var timeoutId = setTimeout(function () {
                            open();
                        }, 2000);
                        setTimer(timeoutId);
                    }
                }, onDragLeave: function () {
                    if (onDragLeave) {
                        onDragLeave();
                    }
                    if (timer !== null) {
                        clearTimeout(timer);
                        setTimer(null);
                    }
                }, onDrop: function (event) {
                    event.stopPropagation();
                    event.preventDefault();
                    event.persist();
                    if (onDrop) {
                        onDrop();
                    }
                    if (timer !== null) {
                        clearTimeout(timer);
                        setTimer(null);
                    }
                }, onDragEnd: function (event) {
                    event.stopPropagation();
                    event.preventDefault();
                    if (onDragEnd) {
                        onDragEnd();
                    }
                    if (timer !== null) {
                        clearTimeout(timer);
                        setTimer(null);
                    }
                } }, { children: [(0, jsx_runtime_1.jsxs)(TreeRow_1.RowInnerContainer, { children: [draggable && ((0, jsx_runtime_1.jsx)(TreeRow_1.DragInitiator, __assign({ ref: dragRef, draggable: true, onDragStart: function (event) {
                                    if (!treeRowRef.current) {
                                        return;
                                    }
                                    event.dataTransfer.setDragImage(treeRowRef.current, 0, 0);
                                    if (!isLeaf && close) {
                                        close();
                                    }
                                    if (onDragStart) {
                                        onDragStart();
                                    }
                                } }, { children: (0, jsx_runtime_1.jsx)(akeneo_design_system_1.RowIcon, { size: 16, shapeRendering: "crispEdges" }, void 0) }), void 0)), (0, jsx_runtime_1.jsx)(TreeRow_1.ArrowButton, __assign({ disabled: isLeaf && !selected, role: "button", onClick: handleArrowClick }, { children: (!isLeaf || selected) && (0, jsx_runtime_1.jsx)(TreeRow_1.TreeArrowIcon, { "$isFolderOpen": isOpen, size: 14 }, void 0) }), void 0), (0, jsx_runtime_1.jsx)(TreeIcon_1.TreeIcon, { isLoading: isLoading, isLeaf: isLeaf && !selected, selected: selected }, void 0), label] }, void 0), actions && !disabled && (0, jsx_runtime_1.jsx)(TreeRow_1.RowActionsContainer, { children: actions }, void 0)] }), void 0), isOpen && !isLeaf && subTrees.length > 0 && ((0, jsx_runtime_1.jsx)(SubTreesContainer, __assign({ role: "group" }, { children: subTrees.map(function (subTree) {
                    return react_1.default.cloneElement(subTree, {
                        key: JSON.stringify(subTree.props.value),
                        _isRoot: false,
                    });
                }) }), void 0))] }), void 0));
    return _isRoot ? (0, jsx_runtime_1.jsx)("ul", __assign({ role: "tree" }, { children: result }), void 0) : result;
};
exports.Tree = Tree;
Tree.displayName = 'Tree';
TreeActions_1.TreeActions.displayName = 'Tree.Actions';
TreeSkeleton_1.TreeSkeleton.displayName = 'Tree.Skeleton';
Tree.Actions = TreeActions_1.TreeActions;
Tree.Skeleton = TreeSkeleton_1.TreeSkeleton;
var templateObject_1, templateObject_2, templateObject_3, templateObject_4;
//# sourceMappingURL=Tree.js.map