import React from 'react';
import '@testing-library/jest-dom';
import {screen, fireEvent} from '@testing-library/react';
import {Dropdown} from '@src/common/components/select/Dropdown';
import {renderWithProviders} from '../../../../test-utils';

describe('Dropdown', () => {
    it('renders its children', () => {
        renderWithProviders(
            <Dropdown onClose={jest.fn()}>
                <span>Dropdown content</span>
            </Dropdown>
        );

        expect(screen.getByText('Dropdown content')).toBeInTheDocument();
    });

    it('calls onClose when mousedown occurs outside the dropdown', () => {
        const onClose = jest.fn();
        renderWithProviders(
            <Dropdown onClose={onClose}>
                <span>Content</span>
            </Dropdown>
        );

        fireEvent.mouseDown(document.body);

        expect(onClose).toHaveBeenCalledTimes(1);
    });

    it('does not call onClose when mousedown occurs inside the dropdown', () => {
        const onClose = jest.fn();
        renderWithProviders(
            <Dropdown onClose={onClose}>
                <span>Content</span>
            </Dropdown>
        );

        fireEvent.mouseDown(screen.getByText('Content'));

        expect(onClose).not.toHaveBeenCalled();
    });

    it('calls onClose when the Escape key is pressed', () => {
        const onClose = jest.fn();
        renderWithProviders(
            <Dropdown onClose={onClose}>
                <span>Content</span>
            </Dropdown>
        );

        fireEvent.keyDown(document, {key: 'Escape'});

        expect(onClose).toHaveBeenCalledTimes(1);
    });

    it('does not call onClose for other key presses', () => {
        const onClose = jest.fn();
        renderWithProviders(
            <Dropdown onClose={onClose}>
                <span>Content</span>
            </Dropdown>
        );

        fireEvent.keyDown(document, {key: 'Enter'});

        expect(onClose).not.toHaveBeenCalled();
    });
});
