import React from 'react';
import '@testing-library/jest-dom';
import {screen} from '@testing-library/react';
import {DashboardProvider, useDashboardState, useDashboardDispatch} from '@src/audit/dashboard-context';
import {renderWithProviders} from '../../test-utils';

const StateDisplay = () => {
    const state = useDashboardState();
    return <div data-testid='state'>{JSON.stringify(state.connections)}</div>;
};

const DispatchDisplay = () => {
    const dispatch = useDashboardDispatch();
    return <div data-testid='dispatch'>{typeof dispatch}</div>;
};

describe('DashboardProvider', () => {
    it('provides an initial empty connections state', () => {
        renderWithProviders(
            <DashboardProvider>
                <StateDisplay />
            </DashboardProvider>
        );

        expect(screen.getByTestId('state')).toHaveTextContent('{}');
    });

    it('provides a dispatch function', () => {
        renderWithProviders(
            <DashboardProvider>
                <DispatchDisplay />
            </DashboardProvider>
        );

        expect(screen.getByTestId('dispatch')).toHaveTextContent('function');
    });

    it('accepts a custom initialState', () => {
        const initialState = {
            connections: {magento: {code: 'magento'} as any},
            events: {} as any,
        };

        renderWithProviders(
            <DashboardProvider initialState={initialState}>
                <StateDisplay />
            </DashboardProvider>
        );

        expect(screen.getByTestId('state').textContent).toContain('magento');
    });
});

describe('useDashboardState', () => {
    it('throws when used outside DashboardProvider', () => {
        const Bad = () => {
            useDashboardState();
            return null;
        };

        expect(() => renderWithProviders(<Bad />)).toThrow('useDashboardState must be used within a DashboardProvider');
    });
});

describe('useDashboardDispatch', () => {
    it('throws when used outside DashboardProvider', () => {
        const Bad = () => {
            useDashboardDispatch();
            return null;
        };

        expect(() => renderWithProviders(<Bad />)).toThrow(
            'useDashboardDispatch must be used within a DashboardProvider'
        );
    });
});
