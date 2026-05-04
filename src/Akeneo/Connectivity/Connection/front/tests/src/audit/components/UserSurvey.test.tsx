import React from 'react';
import '@testing-library/jest-dom';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {UserSurvey} from '@src/audit/components/UserSurvey';
import {renderWithProviders} from '../../../test-utils';

describe('UserSurvey', () => {
    it('renders the title translation key', () => {
        renderWithProviders(<UserSurvey />);

        expect(screen.getByText('akeneo_connectivity.connection.dashboard.user_survey.title')).toBeInTheDocument();
    });

    it('renders the content translation key', () => {
        renderWithProviders(<UserSurvey />);

        expect(screen.getByText('akeneo_connectivity.connection.dashboard.user_survey.content')).toBeInTheDocument();
    });

    it('renders the button translation key', () => {
        renderWithProviders(<UserSurvey />);

        expect(screen.getByText('akeneo_connectivity.connection.dashboard.user_survey.button')).toBeInTheDocument();
    });

    it('opens the survey URL in a new tab when the button is clicked', () => {
        const openMock = jest.fn(() => ({focus: jest.fn()}));
        window.open = openMock as any;

        renderWithProviders(<UserSurvey />);
        userEvent.click(screen.getByText('akeneo_connectivity.connection.dashboard.user_survey.button'));

        expect(openMock).toHaveBeenCalledWith('https://links.akeneo.com/surveys/connection-dashboard', '_blank');
    });
});
