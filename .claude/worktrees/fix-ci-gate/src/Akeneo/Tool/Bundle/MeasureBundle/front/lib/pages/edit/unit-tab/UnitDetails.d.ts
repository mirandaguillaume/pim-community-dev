import React from 'react';
import { MeasurementFamily } from '../../../model/measurement-family';
import { UnitCode } from '../../../model/unit';
import { ValidationError } from '@akeneo-pim-community/shared';
declare type UnitDetailsProps = {
    measurementFamily: MeasurementFamily;
    selectedUnitCode: UnitCode;
    onMeasurementFamilyChange: (measurementFamily: MeasurementFamily) => void;
    selectUnitCode: (unitCode: UnitCode) => void;
    errors: ValidationError[];
};
declare const UnitDetails: ({ measurementFamily, selectedUnitCode, onMeasurementFamilyChange, selectUnitCode, errors, }: UnitDetailsProps) => React.JSX.Element | null;
export { UnitDetails };
