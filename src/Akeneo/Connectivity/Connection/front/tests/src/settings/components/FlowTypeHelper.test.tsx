import React from 'react';
import '@testing-library/jest-dom';
import {screen} from '@testing-library/react';
import {FlowTypeHelper} from '@src/settings/components/FlowTypeHelper';
import {renderWithProviders} from '../../../test-utils';

describe('FlowTypeHelper', () => {
    it('renders the flow type helper message', () => {
        const {container} = renderWithProviders(<FlowTypeHelper />);

        expect(container.textContent).toContain('akeneo_connectivity.connection.flow_type_helper.message');
    });

    it('renders the documentation link with correct href and target', () => {
        renderWithProviders(<FlowTypeHelper />);

        const link = screen.getByRole('link');
        expect(link).toHaveAttribute(
            'href',
            'https://help.akeneo.com/pim/articles/manage-your-connections.html#choose-your-flow-type'
        );
        expect(link).toHaveAttribute('target', '_blank');
        expect(link).toHaveAttribute('rel', 'noopener noreferrer');
    });
});
