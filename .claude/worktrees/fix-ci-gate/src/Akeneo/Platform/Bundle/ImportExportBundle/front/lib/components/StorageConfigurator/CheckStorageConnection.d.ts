import React from 'react';
import { RemoteStorage } from '../../models';
declare type CheckStorageConnectionProps = {
    jobInstanceCode: string;
    storage: RemoteStorage;
};
declare const CheckStorageConnection: ({ jobInstanceCode, storage }: CheckStorageConnectionProps) => React.JSX.Element;
export { CheckStorageConnection };
