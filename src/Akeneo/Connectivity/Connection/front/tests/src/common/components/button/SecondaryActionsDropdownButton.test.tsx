import React from 'react';
import '@testing-library/jest-dom';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {
    SecondaryActionsDropdownButton,
    DropdownLink,
} from '@src/common/components/button/SecondaryActionsDropdownButton';
import {renderWithProviders} from '../../../../test-utils';

describe('SecondaryActionsDropdownButton', () => {
    it('renders the dropdown title translation key', () => {
        renderWithProviders(<SecondaryActionsDropdownButton>Actions</SecondaryActionsDropdownButton>);

        expect(screen.getByText('akeneo_connectivity.connection.secondary_actions.title')).toBeInTheDocument();
    });

    it('renders children inside the dropdown menu', () => {
        renderWithProviders(
            <SecondaryActionsDropdownButton>
                <DropdownLink>Edit</DropdownLink>
            </SecondaryActionsDropdownButton>
        );

        expect(screen.getByText('Edit')).toBeInTheDocument();
    });
});

describe('DropdownLink', () => {
    it('renders as a button element', () => {
        renderWithProviders(<DropdownLink>Delete</DropdownLink>);

        expect(screen.getByRole('button', {name: 'Delete'})).toBeInTheDocument();
    });

    it('has the AknDropdown-menuLink class', () => {
        renderWithProviders(<DropdownLink>Delete</DropdownLink>);

        expect(screen.getByRole('button')).toHaveClass('AknDropdown-menuLink');
    });

    it('calls onClick when clicked', () => {
        const onClick = jest.fn();
        renderWithProviders(<DropdownLink onClick={onClick}>Delete</DropdownLink>);

        userEvent.click(screen.getByRole('button'));

        expect(onClick).toHaveBeenCalledTimes(1);
    });
});
