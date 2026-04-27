import {AttributeGroupCollection} from '../../models';

const FetcherRegistry = require('pim/fetcher-registry');

const fetchAllAttributeGroups = async (): Promise<AttributeGroupCollection> => {
  try {
    return await FetcherRegistry.getFetcher('attribute-group').fetchAll();
  } catch (error) {
    console.error(error);
    return {};
  }
};

export {fetchAllAttributeGroups};
