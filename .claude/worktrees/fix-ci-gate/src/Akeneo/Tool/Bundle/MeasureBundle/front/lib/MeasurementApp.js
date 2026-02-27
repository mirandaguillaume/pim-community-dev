"use strict";
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.MeasurementApp = void 0;
var react_1 = __importDefault(require("react"));
var react_router_dom_1 = require("react-router-dom");
var edit_1 = require("./pages/edit");
var list_1 = require("./pages/list");
var MeasurementApp = function () {
    return (react_1.default.createElement(react_router_dom_1.HashRouter, { basename: "/configuration/measurement" },
        react_1.default.createElement(react_router_dom_1.Switch, null,
            react_1.default.createElement(react_router_dom_1.Route, { path: "/:measurementFamilyCode" },
                react_1.default.createElement(edit_1.Edit, null)),
            react_1.default.createElement(react_router_dom_1.Route, { path: "/" },
                react_1.default.createElement(list_1.List, null)))));
};
exports.MeasurementApp = MeasurementApp;
//# sourceMappingURL=MeasurementApp.js.map