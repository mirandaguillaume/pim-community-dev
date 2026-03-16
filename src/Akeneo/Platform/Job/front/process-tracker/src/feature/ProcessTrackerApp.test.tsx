import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {ProcessTrackerApp} from './ProcessTrackerApp';
import {screen} from '@testing-library/react';

jest.mock('./pages/JobExecutionList', () => ({
  JobExecutionList: () => <>JobExecutionList</>,
}));

jest.mock('./pages/JobExecutionDetail', () => ({
  JobExecutionDetail: () => <>JobExecutionDetail</>,
}));

test('it renders job execution list', () => {
  window.location.hash = '#/job';

  renderWithProviders(<ProcessTrackerApp />);

  expect(screen.getByText('JobExecutionList')).toBeInTheDocument();
});

test('it renders job execution detail', () => {
  window.location.hash = '#/job/show/9999';

  renderWithProviders(<ProcessTrackerApp />);

  expect(screen.getByText('JobExecutionDetail')).toBeInTheDocument();
});
