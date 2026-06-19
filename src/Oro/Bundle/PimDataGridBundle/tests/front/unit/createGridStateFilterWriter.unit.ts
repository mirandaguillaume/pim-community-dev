import createGridStateFilterWriter from '../../../Resources/public/js/datagrid/createGridStateFilterWriter';

const makeCollection = () => ({
  state: {
    currentPage: 5,
    pageSize: 25,
    totalRecords: 100,
    totalPages: 4,
    sorters: {},
    filters: {old: 1},
  },
});

test('setFilters writes the given values into state.filters and touches nothing else', () => {
  const collection = makeCollection();

  createGridStateFilterWriter(collection).setFilters({brand: {type: 'in', value: ['nike']}});

  expect(collection.state.filters).toEqual({brand: {type: 'in', value: ['nike']}});
  expect(collection.state.currentPage).toBe(5); // setFilters must NOT reset the page on its own
  expect(collection.state.pageSize).toBe(25);
  expect(collection.state.totalRecords).toBe(100);
});

test('resetPage sets currentPage to 1 and leaves filters intact', () => {
  const collection = makeCollection();

  createGridStateFilterWriter(collection).resetPage();

  expect(collection.state.currentPage).toBe(1);
  expect(collection.state.filters).toEqual({old: 1}); // resetPage must NOT clear filters
});

test('mutates the same collection reference in place (Strangler shim over the live state object)', () => {
  const collection = makeCollection();
  const writer = createGridStateFilterWriter(collection);

  writer.setFilters({a: 1});
  writer.resetPage();

  expect(collection.state).toMatchObject({filters: {a: 1}, currentPage: 1});
});
