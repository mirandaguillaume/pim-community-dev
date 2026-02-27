"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.isInProgress = exports.isPaused = exports.AVAILABLE_JOB_STATUSES = void 0;
var AVAILABLE_JOB_STATUSES = [
    'ABANDONED',
    'COMPLETED',
    'FAILED',
    'IN_PROGRESS',
    'STARTING',
    'STOPPED',
    'STOPPING',
    'UNKNOWN',
    'PAUSING',
    'PAUSED',
];
exports.AVAILABLE_JOB_STATUSES = AVAILABLE_JOB_STATUSES;
var isPaused = function (status) { return ['PAUSED', 'PAUSING'].includes(status); };
exports.isPaused = isPaused;
var isInProgress = function (status) { return ['STARTING', 'IN_PROGRESS'].includes(status); };
exports.isInProgress = isInProgress;
//# sourceMappingURL=JobStatus.js.map