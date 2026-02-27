"use strict";
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.ProcessTrackerApp = void 0;
var react_1 = __importDefault(require("react"));
var react_router_dom_1 = require("react-router-dom");
var JobExecutionList_1 = require("./pages/JobExecutionList");
var JobExecutionDetail_1 = require("./pages/JobExecutionDetail");
var ProcessTrackerApp = function () { return (react_1.default.createElement(react_router_dom_1.HashRouter, { basename: "/job" },
    react_1.default.createElement(react_router_dom_1.Switch, null,
        react_1.default.createElement(react_router_dom_1.Route, { path: "/show/:jobExecutionId", render: function (props) { return (react_1.default.createElement(JobExecutionDetail_1.JobExecutionDetail, { key: props.match.params.jobExecutionId, jobExecutionId: props.match.params.jobExecutionId })); } }),
        react_1.default.createElement(react_router_dom_1.Route, { path: "/" },
            react_1.default.createElement(JobExecutionList_1.JobExecutionList, null))))); };
exports.ProcessTrackerApp = ProcessTrackerApp;
//# sourceMappingURL=ProcessTrackerApp.js.map