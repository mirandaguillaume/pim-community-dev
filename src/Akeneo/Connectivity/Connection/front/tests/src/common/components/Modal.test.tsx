import React from 'react';
import '@testing-library/jest-dom';
import {screen, fireEvent} from '@testing-library/react';
import {Modal} from '@src/common/components/Modal';
import {renderWithProviders} from '../../../test-utils';

describe('Modal', () => {
    const defaultProps = {
        subTitle: 'Sub title',
        title: 'Modal title',
        description: <p>Modal description</p>,
        onCancel: jest.fn(),
    };

    it('renders the title, subtitle and description', () => {
        renderWithProviders(<Modal {...defaultProps} />);

        expect(screen.getByText('Modal title')).toBeInTheDocument();
        expect(screen.getByText('Sub title')).toBeInTheDocument();
        expect(screen.getByText('Modal description')).toBeInTheDocument();
    });

    it('renders children when provided', () => {
        renderWithProviders(
            <Modal {...defaultProps}>
                <button>Confirm</button>
            </Modal>
        );

        expect(screen.getByRole('button', {name: 'Confirm'})).toBeInTheDocument();
    });

    it('calls onCancel when Escape key is pressed', () => {
        const onCancel = jest.fn();
        renderWithProviders(<Modal {...defaultProps} onCancel={onCancel} />);

        fireEvent.keyDown(document, {code: 'Escape'});

        expect(onCancel).toHaveBeenCalledTimes(1);
    });

    it('calls onCancel when the cancel overlay is clicked', () => {
        const onCancel = jest.fn();
        const {container} = renderWithProviders(<Modal {...defaultProps} onCancel={onCancel} />);

        const cancelOverlay = container.querySelector('.AknFullPage-cancel');
        fireEvent.click(cancelOverlay!);

        expect(onCancel).toHaveBeenCalledTimes(1);
    });
});
