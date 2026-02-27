import React from 'react';
import { JobStatus } from '../../models';
declare type StatusFilterProps = {
    statusFilterValue: JobStatus[];
    onStatusFilterChange: (statusFilterValue: JobStatus[]) => void;
};
declare const StatusFilter: ({ statusFilterValue, onStatusFilterChange }: StatusFilterProps) => React.JSX.Element;
export { StatusFilter };
