import React from 'react';
import {screen} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';

jest.mock('@akeneo-pim-community/settings-ui', () => ({
  CreateAttributeModal: ({onClose}: {onClose: () => void}) => (
    <div data-testid="create-attribute-modal">
      <button onClick={onClose}>Close modal</button>
    </div>
  ),
}));

import {view as CreateAttributeCodeAndLabel} from '../../../../../Resources/public/js/attribute/form/CreateAttributeCodeAndLabel';

test('It renders and delegates to CreateAttributeModal', () => {
  renderWithProviders(<CreateAttributeCodeAndLabel onClose={jest.fn()} onStepConfirm={jest.fn()} />);
  expect(screen.getByTestId('create-attribute-modal')).toBeInTheDocument();
});
