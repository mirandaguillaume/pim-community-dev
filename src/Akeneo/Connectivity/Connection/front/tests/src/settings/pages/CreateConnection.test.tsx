import {ConnectionsProvider} from '@src/settings/connections-context';
import {CreateConnection} from '@src/settings/pages/CreateConnection';
import '@testing-library/jest-dom';
import {screen, waitFor} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import React from 'react';
import {MemoryRouter, Route, Routes} from 'react-router-dom';
import {renderWithProvidersNoRouter, LocationDisplay} from '../../../test-utils';

describe('testing CreateConnection page', () => {
  beforeEach(() => {
    fetchMock.resetMocks();
  });

  it('creates a connection', async () => {
    fetchMock.mockResponseOnce('{}', {status: 201});

    renderWithProvidersNoRouter(
      <MemoryRouter initialEntries={['/connections/create']}>
        <Routes>
          <Route
            path="/connections/create"
            element={
              <ConnectionsProvider>
                <CreateConnection />
              </ConnectionsProvider>
            }
          />
        </Routes>
        <LocationDisplay />
      </MemoryRouter>
    );

    const labelInput = screen.getByLabelText<HTMLInputElement>(/^akeneo_connectivity\.connection\.connection\.label/);
    const codeInput = screen.getByLabelText(/^akeneo_connectivity\.connection\.connection\.code/);
    const flowTypeSelect = screen.getByLabelText(/^akeneo_connectivity\.connection\.connection\.flow_type/);
    const saveButton = screen.getByText('pim_common.save');

    userEvent.clear(labelInput);
    await waitFor(() => expect(labelInput.value).toBe(''));
    userEvent.type(labelInput, 'Magento');

    userEvent.click(flowTypeSelect);
    userEvent.click(await screen.findByText(/akeneo_connectivity\.connection\.flow_type\.data_destination/));
    userEvent.click(saveButton);

    await waitFor(() => expect(fetchMock).toBeCalled());
    expect(fetchMock.mock.calls[0][0]).toEqual('akeneo_connectivity_connection_rest_create');
    expect(fetchMock.mock.calls[0][1]).toMatchObject({
      method: 'POST',
      body: JSON.stringify({
        code: 'magento',
        label: 'Magento',
        flow_type: 'data_destination',
      }),
    });

    await waitFor(() => {
      const locationEl = document.querySelector('[data-testid="location"]');
      expect(locationEl).toHaveTextContent('/connect/connection-settings/magento/edit');
    });
  });
});
