import React from 'react';
import { JobStatus } from '../../models';
declare type JobExecutionStatusProps = {
    status: JobStatus;
    currentStep: number;
    totalSteps: number;
    hasWarning: boolean;
    hasError: boolean;
};
declare const JobExecutionStatus: ({ status, currentStep, totalSteps, hasWarning, hasError, ...props }: JobExecutionStatusProps) => React.JSX.Element;
export { JobExecutionStatus };
