import React from 'react';
import { ButtonProps } from 'akeneo-design-system';
declare type StopJobActionProps = {
    id: string;
    jobLabel: string;
    isStoppable: boolean;
    onStop: () => void;
} & ButtonProps;
declare const StopJobAction: ({ id, jobLabel, isStoppable, onStop, ...rest }: StopJobActionProps) => React.JSX.Element | null;
export { StopJobAction };
