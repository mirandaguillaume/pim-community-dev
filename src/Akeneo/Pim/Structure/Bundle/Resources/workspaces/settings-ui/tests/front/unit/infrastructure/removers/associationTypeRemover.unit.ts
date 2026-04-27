import fetchMock from 'jest-fetch-mock';
import {removeAssociationType} from '@akeneo-pim-community/settings-ui/src/infrastructure/removers/associationTypeRemover';
import {AssociationType} from '@akeneo-pim-community/settings-ui';

const anAssociationType = (): AssociationType => ({
  id: 1,
  label: 'Cross-sell',
  isQuantified: false,
  isTwoWay: false,
  editLink: '/api/association-types/1/edit',
  deleteLink: '/api/association-types/1/delete',
});

beforeEach(() => fetchMock.resetMocks());

test('it returns true when the DELETE request succeeds', async () => {
  fetchMock.mockResponseOnce('', {status: 204});

  const result = await removeAssociationType(anAssociationType());

  expect(result).toBe(true);
  expect(fetchMock).toHaveBeenCalledWith(
    '/api/association-types/1/delete',
    expect.objectContaining({method: 'DELETE'})
  );
});

test('it returns false when the DELETE request fails', async () => {
  fetchMock.mockResponseOnce('', {status: 500});

  const result = await removeAssociationType(anAssociationType());

  expect(result).toBe(false);
});

test('it returns false when the fetch throws an error', async () => {
  fetchMock.mockRejectOnce(new Error('Network error'));

  const result = await removeAssociationType(anAssociationType());

  expect(result).toBe(false);
});
