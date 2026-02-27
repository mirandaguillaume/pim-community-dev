declare const AVAILABLE_JOB_STATUSES: readonly ["ABANDONED", "COMPLETED", "FAILED", "IN_PROGRESS", "STARTING", "STOPPED", "STOPPING", "UNKNOWN", "PAUSING", "PAUSED"];
declare type JobStatus = typeof AVAILABLE_JOB_STATUSES[number];
declare const isPaused: (status: JobStatus) => boolean;
declare const isInProgress: (status: JobStatus) => boolean;
export type { JobStatus };
export { AVAILABLE_JOB_STATUSES, isPaused, isInProgress };
