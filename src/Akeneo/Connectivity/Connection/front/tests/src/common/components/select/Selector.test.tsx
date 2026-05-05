import React from 'react';
import '@testing-library/jest-dom';
import {screen, fireEvent} from '@testing-library/react';
import {Selector} from '@src/common/components/select/Selector';
import {renderWithProviders} from '../../../../test-utils';

describe('Selector', () => {
    it('renders the children text', () => {
        renderWithProviders(<Selector onClick={jest.fn()}>My Option</Selector>);

        expect(screen.getByText('My Option')).toBeInTheDocument();
    });

    it('calls onClick when clicked', () => {
        const onClick = jest.fn();
        renderWithProviders(<Selector onClick={onClick}>Click me</Selector>);

        fireEvent.click(screen.getByText('Click me'));

        expect(onClick).toHaveBeenCalledTimes(1);
    });

    it('renders a down-arrow icon', () => {
        const {container} = renderWithProviders(<Selector onClick={jest.fn()}>Label</Selector>);

        expect(container.querySelector('svg')).toBeInTheDocument();
    });
});
