import React from 'react';
import { MeasurementFamily } from '../../model/measurement-family';
import { ValidationError } from '@akeneo-pim-community/shared';
declare const PropertyTab: ({ measurementFamily, errors, onMeasurementFamilyChange, }: {
    measurementFamily: MeasurementFamily;
    errors: ValidationError[];
    onMeasurementFamilyChange: (measurementFamily: MeasurementFamily) => void;
}) => React.JSX.Element;
export { PropertyTab };
