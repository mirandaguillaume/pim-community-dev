import React from 'react';
import { MeasurementFamilyCode } from '../../model/measurement-family';
declare type CreateMeasurementFamilyProps = {
    isOpen: boolean;
    onClose: (createdMeasurementFamilyCode?: MeasurementFamilyCode) => void;
};
declare const CreateMeasurementFamily: ({ isOpen, onClose }: CreateMeasurementFamilyProps) => React.JSX.Element | null;
export { CreateMeasurementFamily };
