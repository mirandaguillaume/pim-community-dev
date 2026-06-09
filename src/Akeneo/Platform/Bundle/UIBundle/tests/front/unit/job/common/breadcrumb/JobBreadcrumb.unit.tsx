import React from 'react';
import {screen} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import {JobBreadcrumb} from '../../../../../../Resources/public/js/job/common/breadcrumb/JobBreadcrumb';

test('It renders the job label', () => {
  renderWithProviders(<JobBreadcrumb isEdit={false} jobCode="my_export" jobLabel="My Export Job" jobType="export" />);
  expect(screen.getByText('My Export Job')).toBeInTheDocument();
});

test('It renders the job type i18n key', () => {
  renderWithProviders(<JobBreadcrumb isEdit={false} jobCode="my_export" jobLabel="My Export Job" jobType="export" />);
  expect(screen.getByText('pim_menu.tab.exports')).toBeInTheDocument();
});

test('It shows the edit step when isEdit is true', () => {
  renderWithProviders(<JobBreadcrumb isEdit={true} jobCode="my_export" jobLabel="My Export Job" jobType="export" />);
  expect(screen.getByText('pim_common.edit')).toBeInTheDocument();
});

test('It hides the edit step when isEdit is false', () => {
  renderWithProviders(<JobBreadcrumb isEdit={false} jobCode="my_export" jobLabel="My Export Job" jobType="export" />);
  expect(screen.queryByText('pim_common.edit')).not.toBeInTheDocument();
});
