import React from 'react';
import {renderHook, waitFor, act} from '@testing-library/react';
import {QueryClient, QueryClientProvider} from '@tanstack/react-query';
import {NotificationLevel, useNotify, useRouter, useTranslate} from '@akeneo-pim-community/shared';
import {useNavigate} from 'react-router-dom';
import {useTemplateByTemplateUuid} from './useTemplateByTemplateUuid';

jest.mock('@akeneo-pim-community/shared', () => ({
  ...jest.requireActual('@akeneo-pim-community/shared'),
  useRouter: jest.fn(),
  useNotify: jest.fn(),
  useTranslate: jest.fn(),
}));

jest.mock('react-router-dom', () => ({
  ...jest.requireActual('react-router-dom'),
  useNavigate: jest.fn(),
}));

const mockedUseRouter = useRouter as jest.Mock;
const mockedUseNotify = useNotify as jest.Mock;
const mockedUseTranslate = useTranslate as jest.Mock;
const mockedUseNavigate = useNavigate as jest.Mock;

const createWrapper = () => {
  const queryClient = new QueryClient({
    defaultOptions: {queries: {retry: false}, mutations: {retry: false}},
  });
  return ({children}: {children: React.ReactNode}) =>
    React.createElement(QueryClientProvider, {client: queryClient}, children);
};

describe('useTemplateByTemplateUuid', () => {
  const mockNotify = jest.fn();
  const mockNavigate = jest.fn();

  beforeEach(() => {
    mockNotify.mockReset();
    mockNavigate.mockReset();
    mockedUseRouter.mockReturnValue({generate: (route: string) => route});
    mockedUseNotify.mockReturnValue(mockNotify);
    mockedUseTranslate.mockReturnValue((key: string) => key);
    mockedUseNavigate.mockReturnValue(mockNavigate);
  });

  it('does not call fetch when uuid is null (query disabled)', async () => {
    const fetchSpy = jest.spyOn(global, 'fetch').mockResolvedValue({
      ok: true,
      status: 200,
      json: () => Promise.resolve({uuid: 'tmpl-uuid'}),
    } as any);

    renderHook(() => useTemplateByTemplateUuid(null), {wrapper: createWrapper()});

    await act(async () => {
      await new Promise(resolve => setTimeout(resolve, 0));
    });

    expect(fetchSpy).not.toHaveBeenCalled();
  });

  it('calls fetch with the generated route when uuid is provided', async () => {
    const fetchSpy = jest.spyOn(global, 'fetch').mockResolvedValue({
      ok: true,
      status: 200,
      json: () => Promise.resolve({uuid: 'tmpl-uuid'}),
    } as any);

    renderHook(() => useTemplateByTemplateUuid('tmpl-uuid'), {wrapper: createWrapper()});

    await waitFor(() => expect(fetchSpy).toHaveBeenCalled());

    expect(fetchSpy).toHaveBeenCalledWith('pim_category_template_rest_get_by_template_uuid', expect.any(Object));
  });

  it('navigates to / and notifies ERROR when the query fails', async () => {
    jest.spyOn(global, 'fetch').mockResolvedValue({
      ok: false,
      status: 404,
      statusText: 'Not Found',
      json: () => Promise.resolve(undefined),
    } as any);
    jest.spyOn(console, 'error').mockImplementation(() => {});

    const {result} = renderHook(() => useTemplateByTemplateUuid('tmpl-uuid'), {wrapper: createWrapper()});

    await waitFor(() => expect(result.current.isError).toBe(true));

    expect(mockNavigate).toHaveBeenCalledWith('/');
    expect(mockNotify).toHaveBeenCalledWith(NotificationLevel.ERROR, 'akeneo.category.template.not_found');
  });
});
