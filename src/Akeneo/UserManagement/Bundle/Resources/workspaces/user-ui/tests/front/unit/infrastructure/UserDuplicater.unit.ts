import fetchMock from 'jest-fetch-mock';
import {duplicateUser} from '../../../../src/infrastructure/UserDuplicater';

const router = {
  generate: jest.fn().mockReturnValue('/api/users/42/duplicate'),
  redirect: jest.fn(),
  redirectToRoute: jest.fn(),
};

beforeEach(() => {
  fetchMock.resetMocks();
  jest.clearAllMocks();
});

test('it calls the correct URL and returns the fetch response', async () => {
  fetchMock.mockResponseOnce(JSON.stringify({id: 99}), {status: 201});

  const data = {username: 'john_copy', email: 'john@example.com'};
  const result = await duplicateUser(router as any, 42, data);

  expect(router.generate).toHaveBeenCalledWith('pim_user_user_rest_duplicate', {identifier: 42});
  expect(result).not.toBeNull();
});

test('it sends a POST request with JSON body', async () => {
  fetchMock.mockResponseOnce(JSON.stringify({}), {status: 201});

  const data = {username: 'copy'};
  await duplicateUser(router as any, 42, data);

  expect(fetchMock).toHaveBeenCalledWith(
    '/api/users/42/duplicate',
    expect.objectContaining({
      method: 'POST',
      body: JSON.stringify(data),
    })
  );
});

test('it returns null when the fetch throws', async () => {
  fetchMock.mockRejectOnce(new Error('Network error'));

  const result = await duplicateUser(router as any, 42, {});
  expect(result).toBeNull();
});
