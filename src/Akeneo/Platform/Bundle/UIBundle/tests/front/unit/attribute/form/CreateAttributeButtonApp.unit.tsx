import React from 'react';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';

jest.mock('../../../../../Resources/public/js/attribute/form/SelectAttributeType', () => ({
  __esModule: true,
  default: ({onStepConfirm, onClose}: {onStepConfirm: (data: any) => void; onClose: () => void}) => (
    <div data-testid="select-attribute-type">
      <button onClick={() => onStepConfirm({attribute_type: 'pim_catalog_text'})}>Select text type</button>
      <button onClick={onClose}>Close selector</button>
    </div>
  ),
}));

jest.mock('@akeneo-pim-community/settings-ui', () => ({
  CreateAttributeProgressIndicator: () => null,
}));

import {CreateAttributeButtonApp} from '../../../../../Resources/public/js/attribute/form/CreateAttributeButtonApp';

const StepView: React.FC<any> = ({onStepConfirm, onClose}) => (
  <div data-testid="step-view">
    <button onClick={() => onStepConfirm({label: 'My Attribute'})}>Confirm step</button>
    <button onClick={onClose}>Cancel step</button>
  </div>
);

const defaultSteps = {
  default: [{view: StepView}],
};

test('It renders the create button with the given title', () => {
  renderWithProviders(
    <CreateAttributeButtonApp buttonTitle="Create attribute" iconsMap={{}} steps={defaultSteps} onClick={jest.fn()} />
  );
  expect(screen.getByText('Create attribute')).toBeInTheDocument();
});

test('It does not show SelectAttributeType before the button is clicked', () => {
  renderWithProviders(
    <CreateAttributeButtonApp buttonTitle="Create attribute" iconsMap={{}} steps={defaultSteps} onClick={jest.fn()} />
  );
  expect(screen.queryByTestId('select-attribute-type')).not.toBeInTheDocument();
});

test('It shows SelectAttributeType when the create button is clicked', () => {
  renderWithProviders(
    <CreateAttributeButtonApp buttonTitle="Create attribute" iconsMap={{}} steps={defaultSteps} onClick={jest.fn()} />
  );
  userEvent.click(screen.getByText('Create attribute'));
  expect(screen.getByTestId('select-attribute-type')).toBeInTheDocument();
});

test('It advances to the step view after type selection', () => {
  renderWithProviders(
    <CreateAttributeButtonApp buttonTitle="Create attribute" iconsMap={{}} steps={defaultSteps} onClick={jest.fn()} />
  );
  userEvent.click(screen.getByText('Create attribute'));
  userEvent.click(screen.getByText('Select text type'));
  expect(screen.getByTestId('step-view')).toBeInTheDocument();
});

test('It calls onClick with merged data when the last step is confirmed', () => {
  const onClick = jest.fn();
  renderWithProviders(
    <CreateAttributeButtonApp buttonTitle="Create attribute" iconsMap={{}} steps={defaultSteps} onClick={onClick} />
  );
  userEvent.click(screen.getByText('Create attribute'));
  userEvent.click(screen.getByText('Select text type'));
  userEvent.click(screen.getByText('Confirm step'));
  expect(onClick).toHaveBeenCalledWith({attribute_type: 'pim_catalog_text', label: 'My Attribute'});
});
