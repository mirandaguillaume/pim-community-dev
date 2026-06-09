import React from 'react';
import {render, screen} from '@testing-library/react';
import '@testing-library/jest-dom';
import userEvent from '@testing-library/user-event';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import Modal from '../../../../../front/src/application/component/Modal';

jest.mock('oro/translator', () => (key: string) => key, {virtual: true});

const defaultProps = {
  cssClass: 'my-modal',
  title: 'My Title',
  subtitle: 'My Subtitle',
  description: 'My Description',
  illustrationLink: '/img/illustration.svg',
  modalContent: <span>content</span>,
  onConfirm: jest.fn(),
  onDismissModal: jest.fn(),
  enableSaveButton: true,
};

const renderWith = (ui: React.ReactElement) =>
  render(
    <DependenciesProvider>
      <ThemeProvider theme={pimTheme}>{ui}</ThemeProvider>
    </DependenciesProvider>
  );

describe('Modal', () => {
  beforeEach(() => {
    jest.clearAllMocks();
  });

  it('renders title, subtitle and description', () => {
    renderWith(<Modal {...defaultProps} />);
    expect(screen.getByText('My Title')).toBeInTheDocument();
    expect(screen.getByText('My Subtitle')).toBeInTheDocument();
    expect(screen.getByText('My Description')).toBeInTheDocument();
  });

  it('calls onConfirm when save button is clicked and enabled', () => {
    renderWith(<Modal {...defaultProps} />);
    userEvent.click(screen.getByTestId('dqiValidateModal'));
    expect(defaultProps.onConfirm).toHaveBeenCalledTimes(1);
  });

  it('does not call onConfirm when save button is disabled', () => {
    renderWith(<Modal {...defaultProps} enableSaveButton={false} />);
    userEvent.click(screen.getByTestId('dqiValidateModal'));
    expect(defaultProps.onConfirm).not.toHaveBeenCalled();
  });

  it('save button has disabled class when enableSaveButton is false', () => {
    renderWith(<Modal {...defaultProps} enableSaveButton={false} />);
    expect(screen.getByTestId('dqiValidateModal')).toHaveClass('AknButton--disabled');
  });

  it('calls onDismissModal when cancel is clicked', () => {
    const {container} = renderWith(<Modal {...defaultProps} />);
    userEvent.click(container.querySelector('.AknFullPage-cancel')!);
    expect(defaultProps.onDismissModal).toHaveBeenCalledTimes(1);
  });
});
