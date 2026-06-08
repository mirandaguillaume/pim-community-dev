import FetcherRegistry from 'pim/fetcher-registry';

const fetchCategoryTrees = async () => {
  return FetcherRegistry.getFetcher('category').fetchAll();
};

export default fetchCategoryTrees;
