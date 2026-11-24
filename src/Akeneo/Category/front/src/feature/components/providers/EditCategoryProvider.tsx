import React, {createContext, FC, ReactNode, useEffect, useMemo} from 'react';
import {fromPairs} from 'lodash/fp';
import {Channel, Locale, useFetch, useRoute} from '@akeneo-pim-community/shared';

type Channels = {
  [code: string]: Channel;
};

type Locales = {
  [code: string]: Locale;
};

type EditCategoryContextContent = {
  channels: Channels;
  channelsFetchFailed: boolean;
  locales: Locales;
  localesFetchFailed: boolean;
};

const EditCategoryContext = createContext<EditCategoryContextContent>({
  channels: {},
  channelsFetchFailed: false,
  locales: {},
  localesFetchFailed: false,
});

const EditCategoryProvider: FC<{children?: ReactNode}> = ({children}) => {
  const channelsUrl = useRoute('pim_enrich_channel_rest_index');
  const [channelsArray, fetchChannels, statusFetchChannels] = useFetch<Channel[]>(channelsUrl);

  // Memoized on the fetched arrays: useEditCategoryForm re-initializes the edition state -- and
  // so discards unsaved edits -- whenever these change identity. Rebuilt on every render, they
  // did so each time this provider re-rendered.
  const channels: Channels = useMemo(
    () => (channelsArray === null ? {} : fromPairs(channelsArray.map(channel => [channel.code, channel]))),
    [channelsArray]
  );
  const channelsFetchFailed = statusFetchChannels === 'error';

  const localesUrl = useRoute('pim_enrich_locale_rest_index', {activated: 'true'});
  const [localesArray, fetchLocales, statusFetchLocales] = useFetch<Locale[]>(localesUrl);

  const locales: Locales = useMemo(
    () => (localesArray === null ? {} : fromPairs(localesArray.map(locale => [locale.code, locale]))),
    [localesArray]
  );
  const localesFetchFailed = statusFetchLocales === 'error';

  useEffect(() => {
    fetchLocales();
    fetchChannels();
  }, [fetchLocales, fetchChannels]);

  return (
    <EditCategoryContext.Provider value={{channels, channelsFetchFailed, locales, localesFetchFailed}}>
      {children}
    </EditCategoryContext.Provider>
  );
};

export {EditCategoryProvider, EditCategoryContext};
