import { Translate } from '@akeneo-pim-community/shared';
import { JobStatus } from './JobStatus';
import { StepExecutionRowTracking } from './StepExecutionRowTracking';
declare type JobExecutionRowTracking = {
    current_step: number;
    total_step: number;
    steps: StepExecutionRowTracking[];
};
declare type JobExecutionRow = {
    job_execution_id: number;
    job_name: string;
    automation: null | boolean;
    type: string;
    started_at: string | null;
    username: string | null;
    status: JobStatus;
    warning_count: number;
    has_error: boolean;
    tracking: JobExecutionRowTracking;
    is_stoppable: boolean;
};
declare type JobExecutionTable = {
    rows: JobExecutionRow[];
    matches_count: number;
};
declare const jobCanBeStopped: (jobExecutionRow: JobExecutionRow) => boolean;
declare const canShowJobExecutionDetail: (isGranted: (acl: string) => boolean, jobExecutionRow: JobExecutionRow) => boolean;
declare const getJobExecutionRowTrackingProgressLabel: (translate: Translate, jobExecutionRow: JobExecutionRow) => string;
export { jobCanBeStopped, canShowJobExecutionDetail, getJobExecutionRowTrackingProgressLabel };
export type { JobExecutionTable, JobExecutionRow };
