import {act, renderHook, waitFor} from '@testing-library/react';
import {useCreateIdentifierGenerator} from '../useCreateIdentifierGenerator';
import {createWrapper} from '../../tests/hooks/config/createWrapper';
import {mockResponse} from '../../tests/test-utils';
import {AbbreviationType, IdentifierGenerator, PROPERTY_NAMES, TEXT_TRANSFORMATION} from '../../models';
import {InvalidIdentifierGenerator, ServerError} from '../../errors';

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

describe('useCreateIdentifierGenerator', () => {
  it('should create a generator on 201 response', async () => {
    mockResponse('akeneo_identifier_generator_rest_create', 'POST', {
      ok: true,
      status: 201,
      json: generator,
    });

    const {result} = renderHook(() => useCreateIdentifierGenerator(), {wrapper: createWrapper()});

    act(() => {
      result.current.mutate(generator);
    });

    await waitFor(() => {
      expect(fetch).toHaveBeenCalled();
    });
  });

  it('should set error with violations on 400 response', async () => {
    mockResponse('akeneo_identifier_generator_rest_create', 'POST', {
      ok: false,
      status: 400,
      json: {violations: [{path: 'code', message: 'Invalid code'}]},
    });

    const {result} = renderHook(() => useCreateIdentifierGenerator(), {wrapper: createWrapper()});

    act(() => {
      result.current.mutate(generator);
    });

    await waitFor(() => {
      expect(result.current.error).toBeDefined();
    });

    expect(result.current.error).toMatchObject({violations: {violations: [{path: 'code', message: 'Invalid code'}]}});
  });

  it('should throw ServerError on non-201 non-400 response', async () => {
    mockResponse('akeneo_identifier_generator_rest_create', 'POST', {
      ok: false,
      status: 500,
      json: {},
    });

    const {result} = renderHook(() => useCreateIdentifierGenerator(), {wrapper: createWrapper()});

    act(() => {
      result.current.mutate(generator);
    });

    await waitFor(() => {
      expect(fetch).toHaveBeenCalled();
    });
  });

  it('should be initially not loading', () => {
    const {result} = renderHook(() => useCreateIdentifierGenerator(), {wrapper: createWrapper()});
    expect(result.current.isLoading).toBe(false);
  });
});
