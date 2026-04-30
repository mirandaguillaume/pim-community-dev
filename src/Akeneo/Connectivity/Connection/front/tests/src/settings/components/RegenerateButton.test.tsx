import React from 'react';
import '@testing-library/jest-dom';
import {screen, fireEvent} from '@testing-library/react';
import {RegenerateButton} from '@src/settings/components/RegenerateButton';
import {renderWithProviders} from '../../../test-utils';

describe('RegenerateButton', () => {
    it('renders a button with the regenerate title', () => {
        renderWithProviders(<RegenerateButton onClick={jest.fn()} />);

        expect(
            screen.getByTitle('akeneo_connectivity.connection.edit_connection.credentials.action.regenerate')
        ).toBeInTheDocument();
    });

    it('calls onClick when the button is clicked', () => {
        const onClick = jest.fn();
        renderWithProviders(<RegenerateButton onClick={onClick} />);

        fireEvent.click(screen.getByRole('button'));

        expect(onClick).toHaveBeenCalledTimes(1);
    });
});
