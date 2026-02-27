import React from 'react';
import { JobExecutionFilter, JobStatus } from '../../models';
declare type JobExecutionSearchBarProps = {
    jobExecutionFilter: JobExecutionFilter;
    onStatusFilterChange: (status: JobStatus[]) => void;
    onTypeFilterChange: (types: string[]) => void;
    onUserFilterChange: (users: string[]) => void;
    onAutomationFilterChange: (uatomation: null | boolean) => void;
    onSearchChange: (search: string) => void;
};
declare const JobExecutionSearchBar: ({ jobExecutionFilter, onStatusFilterChange, onTypeFilterChange, onUserFilterChange, onAutomationFilterChange, onSearchChange, }: JobExecutionSearchBarProps) => React.JSX.Element;
export { JobExecutionSearchBar };
