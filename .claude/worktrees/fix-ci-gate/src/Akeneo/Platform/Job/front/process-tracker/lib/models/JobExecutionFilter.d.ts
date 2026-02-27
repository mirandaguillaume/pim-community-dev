import { JobStatus } from './JobStatus';
declare type JobExecutionFilterSort = {
    column: string;
    direction: 'ASC' | 'DESC';
};
declare type JobExecutionFilter = {
    page: number;
    size: number;
    sort: JobExecutionFilterSort;
    automation: null | boolean;
    type: string[];
    status: JobStatus[];
    user: string[];
    search: string;
    code: string[];
};
declare const getDefaultJobExecutionFilter: () => JobExecutionFilter;
declare const isDefaultJobExecutionFilter: ({ page, size, type, status, search, user, code }: JobExecutionFilter) => boolean;
export type { JobExecutionFilter, JobExecutionFilterSort };
export { getDefaultJobExecutionFilter, isDefaultJobExecutionFilter };
