import {Connection} from '@src/settings/components/Connection';
import userEvent from '@testing-library/user-event';
import * as React from 'react';
import {MemoryRouter} from 'react-router-dom';
import {act} from '@testing-library/react';
import {renderWithProviders, LocationDisplay} from '../../../test-utils';

describe('Connection', () => {
    it('should redirect to the edit connection page when clicked', async () => {
        const {getByText} = renderWithProviders(
            <MemoryRouter>
                <Connection
                    code={'google-shopping'}
                    label={'Google Shopping'}
                    image={'a/b/c/path.jpg'}
                    hasWrongCombination={false}
                />
                <LocationDisplay />
            </MemoryRouter>
        );

        await act(async () => {
            userEvent.click(getByText('Google Shopping'));

            return Promise.resolve();
        });

        const locationEl = document.querySelector('[data-testid="location"]');
        expect(locationEl).toHaveTextContent('/connect/connection-settings/google-shopping/edit');
    });
});
