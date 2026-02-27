import { JobExecution } from '../models/JobExecutionDetail';
declare type Error = {
    statusMessage: string;
    statusCode: number;
};
declare const useJobExecution: (jobExecutionId: string) => readonly [JobExecution | null, Error | null, () => Promise<void>, boolean];
export { useJobExecution };
