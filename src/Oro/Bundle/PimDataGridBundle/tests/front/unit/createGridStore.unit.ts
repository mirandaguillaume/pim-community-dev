import createGridStore from '../../../Resources/public/js/datagrid/createGridStore';
import {setGridState} from '../../../Resources/public/js/datagrid/gridStateSlice';

const aState = (currentPage: number) => ({
  currentPage,
  pageSize: 25,
  totalRecords: 0,
  totalPages: 1,
  sorters: {},
  filters: {},
});

test('createGridStore returns a fresh, independent store on each call', () => {
  expect(createGridStore()).not.toBe(createGridStore());
});

test('two grid stores are isolated — updating one does not affect the other', () => {
  const storeA = createGridStore();
  const storeB = createGridStore();

  storeA.dispatch(setGridState(aState(7)));
  storeB.dispatch(setGridState(aState(2)));

  expect(storeA.getState().gridState.currentPage).toBe(7);
  expect(storeB.getState().gridState.currentPage).toBe(2);
});

test('a new store starts from the default gridState', () => {
  expect(createGridStore().getState().gridState.currentPage).toBe(1);
});
