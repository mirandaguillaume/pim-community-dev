/* eslint-disable @typescript-eslint/no-unsafe-assignment, @typescript-eslint/no-var-requires,
   @typescript-eslint/no-unsafe-call, @typescript-eslint/no-unsafe-member-access,
   @typescript-eslint/no-unsafe-return */
import {Locale} from '../../models';

import FetcherRegistry from 'pim/fetcher-registry';

const fetchAllLocales = async (): Promise<Locale[]> => {
  try {
    return await FetcherRegistry.getFetcher('locale').fetchAll();
  } catch (error) {
    console.error(error);
    return [];
  }
};
const fetchActivatedLocales = async (): Promise<Locale[]> => {
  try {
    return await FetcherRegistry.getFetcher('locale').fetchActivated({filter_locales: false});
  } catch (error) {
    console.error(error);
    return [];
  }
};

export {fetchAllLocales, fetchActivatedLocales};
