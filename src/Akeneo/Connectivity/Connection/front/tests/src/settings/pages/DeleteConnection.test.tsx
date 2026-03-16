import {act} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import React from 'react';
import {MemoryRouter, Route, Routes} from 'react-router-dom';
import {ConnectionsProvider} from '@src/settings/connections-context';
import {DeleteConnection} from '@src/settings/pages/DeleteConnection';
import {renderWithProviders, LocationDisplay} from '../../../test-utils';

describe('testing DeleteConnection page', () => {
    beforeEach(() => {
        fetchMock.resetMocks();
    });

    it('deletes a connection', async () => {
        fetchMock.mockResponseOnce('', {status: 204});

        const {getByText} = renderWithProviders(
            <MemoryRouter initialEntries={['/connect/connection-settings/franklin/delete']}>
                <Routes>
                    <Route
                        path='/connect/connection-settings/:code/delete'
                        element={
                            <ConnectionsProvider>
                                <DeleteConnection />
                            </ConnectionsProvider>
                        }
                    />
                </Routes>
                <LocationDisplay />
            </MemoryRouter>
        );

        const deleteButton = getByText('pim_common.delete');

        await act(async () => {
            userEvent.click(deleteButton);

            return Promise.resolve();
        });

        expect(fetchMock).toBeCalled();
        expect(fetchMock.mock.calls[0][0]).toEqual('akeneo_connectivity_connection_rest_delete?code=franklin');
        expect(fetchMock.mock.calls[0][1]).toMatchObject({
            method: 'DELETE',
        });

        const locationEl = document.querySelector('[data-testid="location"]');
        expect(locationEl).toHaveTextContent('/connect/connection-settings');
    });
});
