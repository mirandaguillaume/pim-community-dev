import React from 'react';
import {screen} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import {ProductGridProjectDetails} from '../../../../Resources/public/js/grid/ProductGridProjectDetails';

const defaultProps = {
  projectDetails: {
    dueDateLabel: 'Due date',
    dueDate: '2026-12-31',
    completionRatio: 75,
  },
};

test('It renders the completion ratio', () => {
  renderWithProviders(<ProductGridProjectDetails {...defaultProps} />);
  expect(screen.getByText('75 %')).toBeInTheDocument();
});

test('It renders dueDateLabel and dueDate from props', () => {
  const {container} = renderWithProviders(<ProductGridProjectDetails {...defaultProps} />);
  expect(container).toHaveTextContent('Due date');
  expect(container).toHaveTextContent('2026-12-31');
});

test('It renders 0% for zero completion', () => {
  renderWithProviders(
    <ProductGridProjectDetails projectDetails={{dueDateLabel: 'Due date', dueDate: '2026-12-31', completionRatio: 0}} />
  );
  expect(screen.getByText('0 %')).toBeInTheDocument();
});

test('It renders 100% for full completion', () => {
  renderWithProviders(
    <ProductGridProjectDetails
      projectDetails={{dueDateLabel: 'Due date', dueDate: '2026-12-31', completionRatio: 100}}
    />
  );
  expect(screen.getByText('100 %')).toBeInTheDocument();
});
