import React from 'react';
import '@testing-library/jest-dom';
import {screen} from '@testing-library/react';
import {AuditableHelper} from '@src/settings/components/AuditableHelper';
import {renderWithProviders} from '../../../test-utils';

describe('AuditableHelper', () => {
    it('renders the helper message translation key', () => {
        const {container} = renderWithProviders(<AuditableHelper />);

        expect(container.textContent).toContain('akeneo_connectivity.connection.auditable_helper.message');
    });

    it('renders the documentation link with correct href and security attributes', () => {
        renderWithProviders(<AuditableHelper />);

        const link = screen.getByRole('link');
        expect(link).toHaveAttribute(
            'href',
            'https://help.akeneo.com/pim/articles/manage-your-connections.html#enable-the-tracking'
        );
        expect(link).toHaveAttribute('target', '_blank');
        expect(link).toHaveAttribute('rel', 'noopener noreferrer');
    });
});
