import React, { FC } from 'react';
declare type SetCanLeavePage = (canLeavePage: boolean) => void;
declare type SetLeavePageMessage = (message: string) => void;
declare type CanLeavePageContextContent = {
    setCanLeavePage: SetCanLeavePage;
    setLeavePageMessage: SetLeavePageMessage;
};
declare const CanLeavePageContext: React.Context<CanLeavePageContextContent>;
declare type Props = {
    setCanLeavePage: SetCanLeavePage;
    setLeavePageMessage: SetLeavePageMessage;
};
declare const CanLeavePageProvider: FC<Props>;
export { CanLeavePageProvider, CanLeavePageContext };
