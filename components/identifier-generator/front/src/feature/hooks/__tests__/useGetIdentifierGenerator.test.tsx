import {renderHook, waitFor} from '@testing-library/react';
import {useGetIdentifierGenerator} from '../useGetIdentifierGenerator';
import {createWrapper} from '../../tests/hooks/config/createWrapper';
import {mockResponse} from '../../tests/test-utils';
import {IdentifierGeneratorNotFound, ServerError} from '../../errors';
import {IdentifierGenerator, PROPERTY_NAMES, TEXT_TRANSFORMATION} from '../../models';

const generator: IdentifierGenerator = {
  code: 'my_generator',
  target: 'sku',
  structure: [
    {
      type: PROPERTY_NAMES.FREE_TEXT,
      string: 'AKN',
    },
  ],
  delimiter: null,
  labels: {en_US: 'My Generator'},
  conditions: [],
  text_transformation: TEXT_TRANSFORMATION.NO,
};

describe('useGetIdentifierGenerator', () => {
  it('should return generator data on success', async () => {
    mockResponse('akeneo_identifier_generator_rest_get', 'GET', {ok: true, json: generator});

    const {result} = renderHook(() => useGetIdentifierGenerator('my_generator'), {wrapper: createWrapper()});

    await waitFor(() => {
      expect(result.current.data).not.toBeUndefined();
    });

    expect(result.current.data).toEqual(generator);
    expect(result.current.error).toBeNull();
  });

  it('should throw IdentifierGeneratorNotFound on 404', async () => {
    jest.spyOn(console, 'error').mockImplementation(() => null);
    jest.spyOn(global, 'fetch').mockResolvedValueOnce({
      ok: false,
      json: () => Promise.resolve({}),
      status: 404,
      statusText: 'Not Found',
    } as Response);

    const {result} = renderHook(() => useGetIdentifierGenerator('unknown_generator'), {wrapper: createWrapper()});

    await waitFor(() => {
      expect(result.current.error).not.toBeNull();
    });

    expect(result.current.error).toBeInstanceOf(IdentifierGeneratorNotFound);
  });

  it('should throw ServerError on other errors', async () => {
    jest.spyOn(console, 'error').mockImplementation(() => null);
    jest.spyOn(global, 'fetch').mockResolvedValueOnce({
      ok: false,
      json: () => Promise.resolve({}),
      status: 500,
      statusText: 'Internal Server Error',
    } as Response);

    const {result} = renderHook(() => useGetIdentifierGenerator('my_generator'), {wrapper: createWrapper()});

    await waitFor(() => {
      expect(result.current.error).not.toBeNull();
    });

    expect(result.current.error).toBeInstanceOf(ServerError);
  });
});
