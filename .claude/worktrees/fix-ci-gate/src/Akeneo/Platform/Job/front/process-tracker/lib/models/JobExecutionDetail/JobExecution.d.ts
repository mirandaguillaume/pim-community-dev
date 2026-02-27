import { StepExecution } from './StepExecution';
import { Failure } from './Failure';
import { JobStatus } from '../JobStatus';
declare type StepExecutionTracking = {
    hasError: boolean;
    hasWarning: boolean;
    isTrackable: boolean;
    jobName: string;
    stepName: string;
    status: JobStatus;
    duration: number;
    processedItems: number;
    totalItems: number;
};
declare type JobExecutionTracking = {
    error: boolean;
    warning: boolean;
    status: JobStatus;
    currentStep: number;
    totalSteps: number;
    steps: StepExecutionTracking[];
};
declare type JobInstance = {
    label: string;
    code: string;
    type: string;
};
declare type JobExecutionArchives = Record<string, {
    label: string;
    files: Record<string, string>;
}>;
declare type JobExecution = {
    stepExecutions?: StepExecution[];
    failures: Failure[];
    jobInstance: JobInstance;
    tracking: JobExecutionTracking;
    isStoppable: boolean;
    status: string | undefined;
    isRunning: boolean;
    meta: {
        logExists: boolean;
        archives: JobExecutionArchives;
        generateZipArchive?: boolean;
    };
};
declare type DownloadLink = {
    label: string;
    archiver: string;
    key: string;
};
declare const getDownloadLinks: (jobExecutionArchives: JobExecutionArchives | null) => DownloadLink[];
declare const isJobFinished: (jobExecution: JobExecution | null) => boolean;
export { getDownloadLinks, isJobFinished };
export type { JobExecution, JobExecutionArchives, JobExecutionTracking, JobInstance, StepExecutionTracking };
