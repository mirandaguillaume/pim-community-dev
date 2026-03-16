import React from 'react';
import {useNavigate} from 'react-router-dom';
import {Figure, FigureCaption, FigureImage} from '../../common';
import imgUrl from '../../common/assets/illustrations/NewAPI.svg';
import {useMediaUrlGenerator} from '../use-media-url-generator';

interface Props {
    code: string;
    label: string;
    hasWrongCombination: boolean;
    image: string | null;
}

export const Connection = ({code, label, hasWrongCombination, image}: Props) => {
    const navigate = useNavigate();
    const generateMediaUrl = useMediaUrlGenerator();

    return (
        <Figure onClick={() => navigate(`/connect/connection-settings/${code}/edit`)}>
            <FigureImage
                className='AknImage-display'
                src={null === image ? imgUrl : generateMediaUrl(image, 'thumbnail')}
                alt={label}
            />
            <FigureCaption title={label} warning={hasWrongCombination}>
                {label}
            </FigureCaption>
        </Figure>
    );
};
