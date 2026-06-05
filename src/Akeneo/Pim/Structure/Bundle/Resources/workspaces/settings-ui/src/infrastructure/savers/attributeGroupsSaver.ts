import {AttributeGroupCollection} from '../../models';

import FetcherRegistry from 'pim/fetcher-registry';
import Routing from 'routing';

type AttributeGroupsOrder = {
  [code: string]: number;
};

const saveAttributeGroupsOrder = async (sortOrder: AttributeGroupsOrder): Promise<AttributeGroupCollection> => {
  try {
    return fetch(Routing.generate('pim_enrich_attributegroup_rest_sort'), {
      method: 'PATCH',
      headers: [
        ['Content-type', 'application/json'],
        ['X-Requested-With', 'XMLHttpRequest'],
      ],
      body: JSON.stringify(sortOrder),
    }).then(response => {
      FetcherRegistry.getFetcher('attribute-group').clear();

      return response.json();
    });
  } catch (error) {
    console.error(error);
    return Promise.resolve({});
  }
};

export {saveAttributeGroupsOrder};
