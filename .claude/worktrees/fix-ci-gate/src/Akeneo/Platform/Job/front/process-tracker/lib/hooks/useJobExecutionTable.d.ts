import { JobExecutionFilter, JobExecutionTable } from '../models';
declare const useJobExecutionTable: ({ page, size, sort, automation, type, status, code, user, search }: JobExecutionFilter, autoRefresh?: boolean) => readonly [JobExecutionTable | null, () => Promise<void>];
export { useJobExecutionTable };
