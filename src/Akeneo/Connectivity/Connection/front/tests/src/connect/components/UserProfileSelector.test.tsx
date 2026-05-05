import React from 'react';
import '@testing-library/jest-dom';
import {screen} from '@testing-library/react';
import {UserProfileSelector} from '@src/connect/components/UserProfileSelector';
import {renderWithProviders} from '../../../test-utils';

jest.mock('@src/connect/hooks/use-fetch-user-profiles', () => ({
    useFetchUserProfiles: () => jest.fn().mockResolvedValue([]),
}));

describe('UserProfileSelector', () => {
    it('renders the caption', () => {
        renderWithProviders(
            <UserProfileSelector selectedProfile={null} handleOnSelectChange={jest.fn()} handleClick={jest.fn()} />
        );

        expect(screen.getByText('pim_user.profile.caption')).toBeInTheDocument();
    });

    it('renders the field label', () => {
        renderWithProviders(
            <UserProfileSelector selectedProfile={null} handleOnSelectChange={jest.fn()} handleClick={jest.fn()} />
        );

        expect(screen.getByText('pim_user_management.entity.user.properties.profile')).toBeInTheDocument();
    });

    it('renders the why_is_it_needed link', () => {
        renderWithProviders(
            <UserProfileSelector selectedProfile={null} handleOnSelectChange={jest.fn()} handleClick={jest.fn()} />
        );

        expect(screen.getByText('pim_user.profile.why_is_it_needed')).toBeInTheDocument();
    });

    it('renders the save button', () => {
        renderWithProviders(
            <UserProfileSelector selectedProfile={null} handleOnSelectChange={jest.fn()} handleClick={jest.fn()} />
        );

        expect(screen.getByText('pim_user.profile.save_button')).toBeInTheDocument();
    });

    it('disables the save button when selectedProfile is null', () => {
        renderWithProviders(
            <UserProfileSelector selectedProfile={null} handleOnSelectChange={jest.fn()} handleClick={jest.fn()} />
        );

        expect(screen.getByText('pim_user.profile.save_button').closest('button')).toBeDisabled();
    });

    it('enables the save button when selectedProfile is set', () => {
        renderWithProviders(
            <UserProfileSelector selectedProfile='manager' handleOnSelectChange={jest.fn()} handleClick={jest.fn()} />
        );

        expect(screen.getByText('pim_user.profile.save_button').closest('button')).not.toBeDisabled();
    });
});
