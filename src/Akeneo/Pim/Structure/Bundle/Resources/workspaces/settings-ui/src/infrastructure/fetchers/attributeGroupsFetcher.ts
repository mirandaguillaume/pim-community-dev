/* eslint-disable @typescript-eslint/no-unsafe-assignment, @typescript-eslint/no-var-requires,
   @typescript-eslint/no-unsafe-call, @typescript-eslint/no-unsafe-member-access,
   @typescript-eslint/no-unsafe-return */
import {AttributeGroupCollection} from '../../models';

import FetcherRegistry from 'pim/fetcher-registry';

const fetchAllAttributeGroups = async (): Promise<AttributeGroupCollection> => {
  try {
    return await FetcherRegistry.getFetcher('attribute-group').fetchAll();
  } catch (error) {
    console.error(error);
    return {};
  }
};

export {fetchAllAttributeGroups};
