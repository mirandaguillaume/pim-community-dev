import React from 'react';
import { ValidationError } from '@akeneo-pim-community/shared';
import { Operation } from '../../model/operation';
declare type OperationCollectionProps = {
    operations: Operation[];
    errors?: ValidationError[];
    readOnly?: boolean;
    onOperationsChange: (operations: Operation[]) => void;
};
declare const OperationCollection: ({ operations, errors, readOnly, onOperationsChange, }: OperationCollectionProps) => React.JSX.Element;
export { OperationCollection };
