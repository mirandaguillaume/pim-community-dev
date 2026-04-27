import {renderHookWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import fetchMock from 'jest-fetch-mock';
import {act} from '@testing-library/react';
import {useAttributeCodeInput} from '@akeneo-pim-community/settings-ui/src/hooks/attributes/useAttributeCodeInput';

const flushPromises = () => new Promise(setImmediate);

beforeEach(() => {
  fetchMock.resetMocks();
  fetchMock.mockResponse(JSON.stringify([]));
});

test('it returns an empty code when no defaultCode is provided', () => {
  const {result} = renderHookWithProviders(() => useAttributeCodeInput({}));
  const [code, _field, isValid] = result.current;
  expect(code).toBe('');
  expect(isValid).toBe(false);
});

test('it returns the defaultCode when provided', () => {
  const {result} = renderHookWithProviders(() => useAttributeCodeInput({defaultCode: 'my_code'}));
  const [code] = result.current;
  expect(code).toBe('my_code');
});

test('it is invalid when code is empty', () => {
  const {result} = renderHookWithProviders(() => useAttributeCodeInput({}));
  const [_code, _field, isValid] = result.current;
  expect(isValid).toBe(false);
});

test('it generates code from label by replacing special characters with underscores', async () => {
  const {result} = renderHookWithProviders(() => useAttributeCodeInput({generatedFromLabel: 'Hello World!'}));

  await act(async () => {
    await flushPromises();
  });

  const [code] = result.current;
  expect(code).toBe('Hello_World_');
});

test('it marks code as invalid when it contains special characters', async () => {
  fetchMock.mockResponse(JSON.stringify([]));
  const {result} = renderHookWithProviders(() => useAttributeCodeInput({defaultCode: 'invalid-code!'}));

  await act(async () => {
    await flushPromises();
  });

  const [_code, _field, isValid] = result.current;
  expect(isValid).toBe(false);
});

test('it marks reserved keywords as invalid', async () => {
  fetchMock.mockResponse(JSON.stringify([]));
  const {result} = renderHookWithProviders(() => useAttributeCodeInput({defaultCode: 'id'}));

  await act(async () => {
    await flushPromises();
  });

  const [_code, _field, isValid] = result.current;
  expect(isValid).toBe(false);
});

test('it marks code as invalid when already used', async () => {
  fetchMock.mockResponse(JSON.stringify([{code: 'existing_code'}]));
  const {result} = renderHookWithProviders(() => useAttributeCodeInput({defaultCode: 'existing_code'}));

  await act(async () => {
    await flushPromises();
  });

  const [_code, _field, isValid] = result.current;
  expect(isValid).toBe(false);
});
