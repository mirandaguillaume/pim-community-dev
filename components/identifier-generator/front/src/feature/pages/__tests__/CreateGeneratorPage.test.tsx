import React from 'react';
import {mockResponse, render, renderWithoutRouter, screen} from '../../tests/test-utils';
import {CreateGeneratorPage} from '../';
import {MemoryRouter, useLocation} from 'react-router-dom';
import {act, fireEvent, waitFor} from '@testing-library/react';
import initialGenerator from '../../tests/fixtures/initialGenerator';
import {QueryClient, QueryClientProvider} from 'react-query';

jest.mock('../CreateOrEditGeneratorPage');

const LocationDisplay = () => {
  const location = useLocation();
  return <div data-testid="location">{location.pathname}</div>;
};

describe('CreateGeneratorPage', () => {
  it('should create a generator', async () => {
    const expectCall = mockResponse('akeneo_identifier_generator_rest_create', 'POST', {
      status: 201,
      body: {...initialGenerator},
      json: {...initialGenerator},
    });

    const queryClient = new QueryClient();
    const mockedQueryClient = jest.spyOn(queryClient, 'invalidateQueries');
    renderWithoutRouter(
      <QueryClientProvider client={queryClient}>
        <MemoryRouter>
          <CreateGeneratorPage initialGenerator={initialGenerator} />
          <LocationDisplay />
        </MemoryRouter>
      </QueryClientProvider>
    );
    expect(screen.getByText('CreateOrEditGeneratorPage')).toBeInTheDocument();

    act(() => {
      fireEvent.click(screen.getByText('Main button'));
    });

    await waitFor(() => {
      expect(screen.getByTestId('location')).toHaveTextContent('/initialCode');
    });
    expectCall();
    expect(mockedQueryClient).toBeCalledWith('getIdentifierGenerator');
  });

  it('should display validation errors', async () => {
    const violationErrors = [{message: 'a message', path: 'a path'}, {message: 'another message'}];

    mockResponse('akeneo_identifier_generator_rest_create', 'POST', {json: violationErrors, status: 400});

    render(<CreateGeneratorPage initialGenerator={initialGenerator} />);
    expect(screen.getByText('CreateOrEditGeneratorPage')).toBeInTheDocument();

    act(() => {
      fireEvent.click(screen.getByText('Main button'));
    });

    expect(await screen.findByText('a path a message')).toBeInTheDocument();
    expect(await screen.findByText('another message')).toBeInTheDocument();
  });

  it('should manage default errors', async () => {
    const expectCall = mockResponse('akeneo_identifier_generator_rest_create', 'POST', {
      ok: false,
      status: 500,
      body: {...initialGenerator},
    });

    render(<CreateGeneratorPage initialGenerator={initialGenerator} />);
    expect(screen.getByText('CreateOrEditGeneratorPage')).toBeInTheDocument();

    act(() => {
      fireEvent.click(screen.getByText('Main button'));
    });

    await waitFor(() => {
      expectCall();
    });
  });

  it('should check generator validation on save', () => {
    render(<CreateGeneratorPage initialGenerator={{...initialGenerator, structure: []}} />);
    expect(screen.getByText('CreateOrEditGeneratorPage')).toBeInTheDocument();

    act(() => {
      fireEvent.click(screen.getByText('Main button'));
    });

    expect(screen.getByText('structure The structure must contain at least one property')).toBeInTheDocument();
  });
});
