import React from 'react';
import {render, screen} from '@testing-library/react';
import '@testing-library/jest-dom';

const mockIsEnabled = jest.fn(() => false);
jest.mock('pim/feature-flags', () => ({isEnabled: mockIsEnabled}), {virtual: true});

import DashboardHelper from '../../../../../../front/src/application/component/Dashboard/DashboardHelper';

describe('DashboardHelper', () => {
  beforeEach(() => {
    localStorage.clear();
    mockIsEnabled.mockReturnValue(false);
  });

  it('shows the helper banner on first visit (no localStorage entry, free_trial disabled)', () => {
    render(<DashboardHelper />);

    expect(screen.getByText('akeneo_data_quality_insights.dqi_dashboard.helper.title')).toBeInTheDocument();
  });

  it('sets localStorage to "0" when showing the helper for the first time', () => {
    render(<DashboardHelper />);

    expect(localStorage.getItem('data-quality-insights:dashboard:show-helper')).toBe('0');
  });

  it('hides the helper when localStorage entry already exists', () => {
    localStorage.setItem('data-quality-insights:dashboard:show-helper', '0');

    render(<DashboardHelper />);

    expect(screen.queryByText('akeneo_data_quality_insights.dqi_dashboard.helper.title')).not.toBeInTheDocument();
  });

  it('hides the helper when the free_trial feature flag is enabled', () => {
    mockIsEnabled.mockReturnValue(true);

    render(<DashboardHelper />);

    expect(screen.queryByText('akeneo_data_quality_insights.dqi_dashboard.helper.title')).not.toBeInTheDocument();
  });

  it('renders a link to the help center when helper is visible', () => {
    render(<DashboardHelper />);

    const link = screen.getByRole('link');
    expect(link).toHaveAttribute('target', '_blank');
    expect(link).toHaveAttribute('href', 'https://help.akeneo.com/pim/articles/understand-data-quality.html');
  });
});
