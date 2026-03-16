import {act} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import React from 'react';
import {MemoryRouter, Route, Routes} from 'react-router-dom';
import {ConnectionsProvider} from '@src/settings/connections-context';
import {RegenerateConnectionPassword} from '@src/settings/pages/RegenerateConnectionPassword';
import {renderWithProviders, LocationDisplay} from '../../../test-utils';

describe('testing RegenerateConnectionPassword page', () => {
  beforeEach(() => {
    fetchMock.resetMocks();
  });

  it('regenerates a connection password', async () => {
    fetchMock.mockResponseOnce('{}');

    const {getByText} = renderWithProviders(
      <MemoryRouter initialEntries={['/connections/franklin/regenerate-password']}>
        <Routes>
          <Route
            path="/connections/:code/regenerate-password"
            element={
              <ConnectionsProvider>
                <RegenerateConnectionPassword />
              </ConnectionsProvider>
            }
          />
        </Routes>
        <LocationDisplay />
      </MemoryRouter>
    );

    const regenerateButton = getByText('akeneo_connectivity.connection.regenerate_password.action.regenerate');

    await act(async () => {
      userEvent.click(regenerateButton);

      return Promise.resolve();
    });

    expect(fetchMock).toBeCalled();
    expect(fetchMock.mock.calls[0][0]).toEqual('akeneo_connectivity_connection_rest_regenerate_password?code=franklin');
    expect(fetchMock.mock.calls[0][1]).toMatchObject({
      method: 'POST',
    });

    const locationEl = document.querySelector('[data-testid="location"]');
    expect(locationEl).toHaveTextContent('/connect/connection-settings/franklin/edit');
  });
});
