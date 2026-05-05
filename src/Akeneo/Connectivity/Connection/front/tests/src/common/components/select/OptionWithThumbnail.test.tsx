import React from 'react';
import '@testing-library/jest-dom';
import {screen, fireEvent} from '@testing-library/react';
import {OptionWithThumbnail} from '@src/common/components/select/OptionWithThumbnail';
import {renderWithProviders} from '../../../../test-utils';

const wrap = (node: React.ReactElement) => <ul>{node}</ul>;

describe('OptionWithThumbnail', () => {
    it('renders the label', () => {
        renderWithProviders(wrap(<OptionWithThumbnail value='v1' onClick={jest.fn()} data={{label: 'My Label'}} />));

        expect(screen.getByText('My Label')).toBeInTheDocument();
    });

    it('renders the thumbnail image', () => {
        renderWithProviders(
            wrap(
                <OptionWithThumbnail
                    value='v1'
                    onClick={jest.fn()}
                    data={{label: 'Item', imageSrc: 'https://example.com/img.png'}}
                />
            )
        );

        expect(screen.getByRole('img')).toHaveAttribute('src', 'https://example.com/img.png');
    });

    it('calls onClick with the value when clicked', () => {
        const onClick = jest.fn();
        renderWithProviders(wrap(<OptionWithThumbnail value='my-value' onClick={onClick} data={{label: 'Option'}} />));

        fireEvent.click(screen.getByText('Option'));

        expect(onClick).toHaveBeenCalledWith('my-value');
    });

    it('renders the title attribute equal to the label', () => {
        const {container} = renderWithProviders(
            wrap(<OptionWithThumbnail value='v1' onClick={jest.fn()} data={{label: 'My Label'}} />)
        );

        expect(container.querySelector('[title="My Label"]')).toBeInTheDocument();
    });
});
