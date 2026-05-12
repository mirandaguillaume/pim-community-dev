import React from 'react';
import {renderHook, act} from '@testing-library/react';
import {QueryClient, QueryClientProvider} from '@tanstack/react-query';
import {NotificationLevel, useNotify, useRoute, useTranslate} from '@akeneo-pim-community/shared';
import {useNavigate} from 'react-router-dom';
import {useDeactivateTemplate} from './useDeactivateTemplate';

jest.mock('@akeneo-pim-community/shared', () => ({
  ...jest.requireActual('@akeneo-pim-community/shared'),
  useNotify: jest.fn(),
  useTranslate: jest.fn(),
  useRoute: jest.fn(),
}));

jest.mock('react-router-dom', () => ({
  ...jest.requireActual('react-router-dom'),
  useNavigate: jest.fn(),
}));

const mockedUseNotify = useNotify as jest.Mock;
const mockedUseTranslate = useTranslate as jest.Mock;
const mockedUseRoute = useRoute as jest.Mock;
const mockedUseNavigate = useNavigate as jest.Mock;

const createWrapper = () => {
  const queryClient = new QueryClient({defaultOptions: {mutations: {retry: false}}});
  return ({children}: {children: React.ReactNode}) =>
    React.createElement(QueryClientProvider, {client: queryClient}, children);
};

describe('useDeactivateTemplate', () => {
  const mockNotify = jest.fn();
  const mockNavigate = jest.fn();

  beforeEach(() => {
    mockNotify.mockReset();
    mockNavigate.mockReset();
    mockedUseNotify.mockReturnValue(mockNotify);
    mockedUseTranslate.mockReturnValue((key: string) => key);
    mockedUseRoute.mockReturnValue('/api/template/tmpl-uuid/deactivate');
    mockedUseNavigate.mockReturnValue(mockNavigate);
    jest.spyOn(global, 'fetch').mockResolvedValue({
      ok: true,
      status: 204,
      json: () => Promise.resolve(undefined),
    } as any);
  });

  it('calls DELETE on the template route', async () => {
    const fetchSpy = jest.spyOn(global, 'fetch');
    const {result} = renderHook(() => useDeactivateTemplate({id: 'tmpl-uuid', label: 'My Template'}), {
      wrapper: createWrapper(),
    });

    await act(async () => {
      await result.current();
    });

    expect(fetchSpy).toHaveBeenCalledWith(
      '/api/template/tmpl-uuid/deactivate',
      expect.objectContaining({method: 'DELETE'})
    );
  });

  it('notifies success with template label', async () => {
    const {result} = renderHook(() => useDeactivateTemplate({id: 'tmpl-uuid', label: 'My Template'}), {
      wrapper: createWrapper(),
    });

    await act(async () => {
      await result.current();
    });

    expect(mockNotify).toHaveBeenCalledWith(
      NotificationLevel.SUCCESS,
      'akeneo.category.template.deactivate.notification_success.title',
      'akeneo.category.template.deactivate.notification_success.message'
    );
  });

  it('navigates to / after successful deactivation', async () => {
    const {result} = renderHook(() => useDeactivateTemplate({id: 'tmpl-uuid', label: 'My Template'}), {
      wrapper: createWrapper(),
    });

    await act(async () => {
      await result.current();
    });

    expect(mockNavigate).toHaveBeenCalledWith('/');
  });

  it('navigates to / even when DELETE fails (finally block)', async () => {
    jest.spyOn(global, 'fetch').mockResolvedValue({
      ok: false,
      status: 500,
      statusText: 'Internal Server Error',
      json: () => Promise.resolve(undefined),
    } as any);
    jest.spyOn(console, 'error').mockImplementation(() => {});

    const {result} = renderHook(() => useDeactivateTemplate({id: 'tmpl-uuid', label: 'My Template'}), {
      wrapper: createWrapper(),
    });

    await act(async () => {
      try {
        await result.current();
      } catch {
        // error expected — the finally block must still navigate
      }
    });

    expect(mockNavigate).toHaveBeenCalledWith('/');
    expect(mockNotify).not.toHaveBeenCalled();
  });
});
