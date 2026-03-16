import React from 'react';
import '@testing-library/jest-dom';
import fetchMock from 'jest-fetch-mock';
import {renderWithProviders} from '../../../test-utils';
import {waitFor} from '@testing-library/react';
import {PermissionsForm} from '@src/connect/components/PermissionsForm';

beforeEach(() => {
    fetchMock.resetMocks();
});

test('The permissions form renders', async () => {
    const myProvider = {
        key: 'myProviderKey',
        label: 'My Provider',
        renderForm: jest.fn(),
        renderSummary: jest.fn(),
        save: jest.fn(),
        loadPermissions: jest.fn(),
    };

    renderWithProviders(
        <PermissionsForm
            provider={myProvider}
            permissions={undefined}
            onPermissionsChange={jest.fn()}
            readOnly={undefined}
            onlyDisplayViewPermissions={false}
        />
    );

    await waitFor(() => expect(myProvider.renderForm).toHaveBeenCalledTimes(1));
});
