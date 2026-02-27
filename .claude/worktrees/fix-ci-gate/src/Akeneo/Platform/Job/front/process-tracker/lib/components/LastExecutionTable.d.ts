import React from 'react';
import { JobExecutionRow } from '../models';
declare type LastExecutionsTableProps = {
    sticky?: number;
    jobExecutionRows: JobExecutionRow[];
    onTableRefresh: () => void;
};
declare const LastExecutionTable: ({ sticky, jobExecutionRows, onTableRefresh }: LastExecutionsTableProps) => React.JSX.Element;
export { LastExecutionTable };
