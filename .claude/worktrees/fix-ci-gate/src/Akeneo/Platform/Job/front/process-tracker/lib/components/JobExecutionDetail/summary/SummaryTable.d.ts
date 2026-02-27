import React from 'react';
import { JobExecution } from '../../../models';
declare type SummaryTableProps = {
    jobExecution: JobExecution;
};
declare const SummaryTable: ({ jobExecution }: SummaryTableProps) => React.JSX.Element | null;
export { SummaryTable };
