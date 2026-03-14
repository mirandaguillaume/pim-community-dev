import React from 'react';
import '@testing-library/jest-dom';
import {act, screen, waitFor} from '@testing-library/react';
import {renderWithProviders} from '../../../../../../test-utils';
import {ConsentCheckbox, sanitizeUrl} from '@src/connect/components/AppWizard/steps/Authentication/ConsentCheckbox';
import userEvent from '@testing-library/user-event';

test('it renders correctly', async () => {
    renderWithProviders(
        <ConsentCheckbox isChecked={false} onChange={() => null} appUrl={null} displayCheckbox={true} />
    );

    await waitFor(() => screen.queryByRole('checkbox'));

    const label = 'akeneo_connectivity.connection.connect.apps.wizard.authentication.consent.label';
    const subtext = 'akeneo_connectivity.connection.connect.apps.wizard.authentication.consent.subtext';
    expect(screen.queryByText(label, {exact: false})).toBeInTheDocument();
    expect(screen.queryByText(subtext, {exact: false})).toBeInTheDocument();
    expect(screen.queryByRole('checkbox', {checked: false})).toBeInTheDocument();
});

test('it renders correctly when checked', async () => {
    renderWithProviders(
        <ConsentCheckbox isChecked={true} onChange={() => null} appUrl={null} displayCheckbox={true} />
    );

    await waitFor(() => screen.queryByRole('checkbox'));

    expect(screen.queryByRole('checkbox', {checked: true})).toBeInTheDocument();
});

test('it calls onChange when checked', async () => {
    const onChange = jest.fn();
    renderWithProviders(<ConsentCheckbox isChecked={false} onChange={onChange} appUrl={null} displayCheckbox={true} />);

    await waitFor(() => screen.queryByRole('checkbox'));
    expect(screen.queryByRole('checkbox', {checked: false})).toBeInTheDocument();

    act(() => userEvent.click(screen.getByRole('checkbox')));

    expect(onChange).toHaveBeenCalledWith(true, expect.anything());
});

test('it calls onChange when unchecked', async () => {
    const onChange = jest.fn();
    renderWithProviders(<ConsentCheckbox isChecked={true} onChange={onChange} appUrl={null} displayCheckbox={true} />);

    await waitFor(() => screen.queryByRole('checkbox'));
    expect(screen.queryByRole('checkbox', {checked: true})).toBeInTheDocument();

    act(() => userEvent.click(screen.getByRole('checkbox')));

    expect(onChange).toHaveBeenCalledWith(false, expect.anything());
});

test('it renders with an appUrl provided', async () => {
    renderWithProviders(
        <ConsentCheckbox isChecked={true} onChange={() => null} appUrl={'https://example.com'} displayCheckbox={true} />
    );

    await waitFor(() => screen.queryByRole('checkbox'));

    const label = 'akeneo_connectivity.connection.connect.apps.wizard.authentication.consent.label';
    expect(screen.queryByText(label, {exact: false})).toBeInTheDocument();
    expect(screen.queryByRole('checkbox', {checked: true})).toBeInTheDocument();
});

test('sanitizeUrl rejects javascript: URLs', () => {
    expect(sanitizeUrl("javascript:alert('xss')")).toBe('#');
});

test('sanitizeUrl rejects data: URLs', () => {
    expect(sanitizeUrl('data:text/html,<script>alert(1)</script>')).toBe('#');
});

test('sanitizeUrl allows valid https URLs', () => {
    expect(sanitizeUrl('https://marketplace.akeneo.com/app')).toBe('https://marketplace.akeneo.com/app');
});

test('sanitizeUrl allows valid http URLs', () => {
    expect(sanitizeUrl('http://example.com')).toBe('http://example.com');
});

test('sanitizeUrl returns # for null', () => {
    expect(sanitizeUrl(null)).toBe('#');
});

test('sanitizeUrl escapes single quotes', () => {
    expect(sanitizeUrl("https://example.com/path?q='test'")).toBe('https://example.com/path?q=&#39;test&#39;');
});

test('it renders correctly when the checkbox must be hidden', async () => {
    renderWithProviders(
        <ConsentCheckbox isChecked={false} onChange={() => null} appUrl={null} displayCheckbox={false} />
    );

    await waitFor(() => screen.queryByRole('checkbox'));

    const label = 'akeneo_connectivity.connection.connect.apps.wizard.authentication.consent.label';
    const subtext = 'akeneo_connectivity.connection.connect.apps.wizard.authentication.consent.subtext';
    expect(screen.queryByText(label, {exact: false})).not.toBeInTheDocument();
    expect(screen.queryByText(subtext, {exact: false})).toBeInTheDocument();
    expect(screen.queryByRole('checkbox', {checked: false})).not.toBeInTheDocument();
});
