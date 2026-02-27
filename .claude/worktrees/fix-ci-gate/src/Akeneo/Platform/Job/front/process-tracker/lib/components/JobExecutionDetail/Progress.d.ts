import React from 'react';
import { StepExecutionTracking } from '../../models';
declare const Progress: ({ jobStatus, steps }: {
    jobStatus: string | undefined;
    steps: StepExecutionTracking[];
}) => React.JSX.Element;
export { Progress };
