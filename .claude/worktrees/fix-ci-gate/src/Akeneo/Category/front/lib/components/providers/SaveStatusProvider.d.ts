import React from 'react';
declare enum Status {
    SAVED = 0,
    ERRORS = 1,
    SAVING = 2,
    EDITING = 3
}
declare type SaveStatusState = {
    globalStatus: number;
    handleStatusListChange: (id: string, status: Status) => void;
};
declare const SaveStatusContext: React.Context<SaveStatusState | null>;
declare type Props = {
    children: React.ReactNode;
};
declare const SaveStatusProvider: ({ children }: Props) => JSX.Element;
export { SaveStatusContext, SaveStatusProvider, Status };
