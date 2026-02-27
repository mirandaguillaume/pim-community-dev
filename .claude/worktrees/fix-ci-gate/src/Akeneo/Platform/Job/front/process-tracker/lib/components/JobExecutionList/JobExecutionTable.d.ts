import React from 'react';
import { JobExecutionRow, JobExecutionFilterSort } from '../../models';
declare type JobExecutionTableProps = {
    sticky?: number;
    jobExecutionRows: JobExecutionRow[];
    onSortChange: (sort: JobExecutionFilterSort) => void;
    onTableRefresh: () => void;
    currentSort: JobExecutionFilterSort;
};
declare const JobExecutionTable: ({ sticky, jobExecutionRows, onSortChange, currentSort, onTableRefresh, }: JobExecutionTableProps) => React.JSX.Element;
export { JobExecutionTable };
