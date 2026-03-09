import React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {render, screen} from '@testing-library/react';
import EventLogDatetime from '@src/webhook/components/EventLogDatetime';
import {UserInterface, UserContext} from '@src/shared/user';
import {ThemeProvider} from 'styled-components';
import {theme} from '@src/common/styled-with-theme';

const renderWithContext = (ui: React.ReactElement) => {
    const user = {
        get: jest.fn((key: string) => {
            if (key === 'uiLocale') return 'en_US';
            if (key === 'timezone') return 'UTC';

            return '';
        }),
        set: jest.fn(),
        refresh: jest.fn(),
    } as unknown as UserInterface;

    return render(
        <UserContext.Provider value={user}>
            <ThemeProvider theme={theme}>{ui}</ThemeProvider>
        </UserContext.Provider>
    );
};

describe('it displays datetime according to a timestamp in second', () => {
    test('the datetime is display with good formatting', () => {
        renderWithContext(<EventLogDatetime timestamp={1615994468000} />);

        expect(screen.getByText('03/17/2021', {exact: false})).toBeInTheDocument();
        expect(screen.getByText('03:21:08 PM', {exact: false})).toBeInTheDocument();
    });
});
