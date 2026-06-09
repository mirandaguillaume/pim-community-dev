import React from 'react';
import {render, screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import '@testing-library/jest-dom';
import {pimTheme} from 'akeneo-design-system';
import {ThemeProvider} from 'styled-components';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';

jest.mock('oro/translator', () => (key: string) => key, {virtual: true});
jest.mock('pim/security-context', () => ({isGranted: jest.fn()}), {virtual: true});
jest.mock('../../../../../../front/src/infrastructure/hooks/AttributeGroup/useAttributeGroupState', () => ({
  useAttributeGroupState: jest.fn(),
}));

import {ToggleActivation} from '../../../../../../front/src/application/component/AttributeGroup/ToggleActivation';
import {useAttributeGroupState} from '../../../../../../front/src/infrastructure/hooks/AttributeGroup/useAttributeGroupState';

const mockUseAttributeGroupState = useAttributeGroupState as jest.Mock;
const mockIsGranted = require('pim/security-context').isGranted as jest.Mock;

describe('ToggleActivation', () => {
  const mockToggle = jest.fn();

  beforeEach(() => {
    jest.clearAllMocks();
    mockUseAttributeGroupState.mockReturnValue({isGroupActivated: true, toggleGroupActivation: mockToggle});
    mockIsGranted.mockReturnValue(true);
  });

  it('renders a checked checkbox when the group is activated', () => {
    render(
      <DependenciesProvider>
        <ThemeProvider theme={pimTheme}>
          <ToggleActivation groupCode="marketing" />
        </ThemeProvider>
      </DependenciesProvider>
    );

    expect(screen.getByRole('checkbox')).toBeChecked();
  });

  it('renders an unchecked checkbox when the group is deactivated', () => {
    mockUseAttributeGroupState.mockReturnValue({isGroupActivated: false, toggleGroupActivation: mockToggle});

    render(
      <DependenciesProvider>
        <ThemeProvider theme={pimTheme}>
          <ToggleActivation groupCode="marketing" />
        </ThemeProvider>
      </DependenciesProvider>
    );

    expect(screen.getByRole('checkbox')).not.toBeChecked();
  });

  it('calls toggleGroupActivation when the label is clicked and the user is granted', () => {
    render(
      <DependenciesProvider>
        <ThemeProvider theme={pimTheme}>
          <ToggleActivation groupCode="marketing" />
        </ThemeProvider>
      </DependenciesProvider>
    );

    userEvent.click(document.querySelector('label.switch-small')!);
    expect(mockToggle).toHaveBeenCalledTimes(1);
  });

  it('does not call toggleGroupActivation when the user is not granted', () => {
    mockIsGranted.mockReturnValue(false);

    render(
      <DependenciesProvider>
        <ThemeProvider theme={pimTheme}>
          <ToggleActivation groupCode="marketing" />
        </ThemeProvider>
      </DependenciesProvider>
    );

    userEvent.click(document.querySelector('label.switch-small')!);
    expect(mockToggle).not.toHaveBeenCalled();
  });
});
