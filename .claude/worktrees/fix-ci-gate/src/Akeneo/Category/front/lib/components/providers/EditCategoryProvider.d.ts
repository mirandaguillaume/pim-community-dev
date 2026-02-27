import React, { FC } from 'react';
import { Channel, Locale } from '@akeneo-pim-community/shared';
declare type Channels = {
    [code: string]: Channel;
};
declare type Locales = {
    [code: string]: Locale;
};
declare type EditCategoryContextContent = {
    channels: Channels;
    channelsFetchFailed: boolean;
    locales: Locales;
    localesFetchFailed: boolean;
};
declare const EditCategoryContext: React.Context<EditCategoryContextContent>;
declare const EditCategoryProvider: FC;
export { EditCategoryProvider, EditCategoryContext };
