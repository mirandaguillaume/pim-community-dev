import { JobExecutionFilter } from '../models';
declare const useStoredJobExecutionFilter: () => readonly [{
    page: number;
    size: number;
    sort: import("../models/JobExecutionFilter").JobExecutionFilterSort;
    automation: boolean | null;
    type: string[];
    status: ("ABANDONED" | "COMPLETED" | "FAILED" | "IN_PROGRESS" | "STARTING" | "STOPPED" | "STOPPING" | "UNKNOWN" | "PAUSING" | "PAUSED")[];
    user: string[];
    search: string;
    code: string[];
}, import("react").Dispatch<import("react").SetStateAction<JobExecutionFilter>>];
export { useStoredJobExecutionFilter };
