import { Level, ProgressBarPercent } from 'akeneo-design-system';
import { StepStatus } from './StepStatus';
declare type StepExecutionRowTracking = {
    has_error: boolean;
    warning_count: number;
    is_trackable: boolean;
    processed_items: number;
    total_items: number;
    status: StepStatus;
    duration: number;
};
declare const getStepExecutionRowTrackingLevel: ({ warning_count, has_error, status }: StepExecutionRowTracking) => Level;
declare const getStepExecutionRowTrackingPercent: (step: StepExecutionRowTracking) => ProgressBarPercent;
export { getStepExecutionRowTrackingLevel, getStepExecutionRowTrackingPercent };
export type { StepExecutionRowTracking };
