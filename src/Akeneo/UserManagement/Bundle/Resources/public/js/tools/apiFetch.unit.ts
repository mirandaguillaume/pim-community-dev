import fetchMock from 'jest-fetch-mock';
import {apiFetch, BadRequestError, ForbiddenError} from './apiFetch';

beforeEach(() => fetchMock.resetMocks());

test('it returns the parsed JSON on a successful response', async () => {
  const data = {id: 1, username: 'john'};
  fetchMock.mockResponseOnce(JSON.stringify(data), {status: 200});

  const result = await apiFetch<typeof data>('/api/users', {method: 'GET'});
  expect(result).toEqual(data);
});

test('it always sends Content-Type and X-Requested-With headers', async () => {
  fetchMock.mockResponseOnce(JSON.stringify({}), {status: 200});

  await apiFetch('/api/users', {method: 'GET'});

  expect(fetchMock).toHaveBeenCalledWith(
    '/api/users',
    expect.objectContaining({
      headers: expect.objectContaining({
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      }),
    })
  );
});

test('it throws BadRequestError with parsed body on a 400 response', async () => {
  const errorData = {message: 'Invalid data'};
  fetchMock.mockResponseOnce(JSON.stringify(errorData), {status: 400});

  await expect(apiFetch('/api/users', {})).rejects.toMatchObject({
    data: errorData,
  });
});

test('it throws an instance of BadRequestError on a 400 response', async () => {
  fetchMock.mockResponseOnce(JSON.stringify({}), {status: 400});

  await expect(apiFetch('/api/users', {})).rejects.toBeInstanceOf(BadRequestError);
});

test('it throws BadRequestError on a 422 response', async () => {
  fetchMock.mockResponseOnce(JSON.stringify({violations: []}), {status: 422});

  await expect(apiFetch('/api/users', {})).rejects.toBeInstanceOf(BadRequestError);
});

test('it throws ForbiddenError on a 403 response', async () => {
  fetchMock.mockResponseOnce('', {status: 403});

  await expect(apiFetch('/api/users', {})).rejects.toBeInstanceOf(ForbiddenError);
});

test('it throws a generic Error with status text for other error codes', async () => {
  fetchMock.mockResponseOnce('', {status: 500, statusText: 'Internal Server Error'});

  await expect(apiFetch('/api/users', {})).rejects.toThrow('500 Internal Server Error');
});
