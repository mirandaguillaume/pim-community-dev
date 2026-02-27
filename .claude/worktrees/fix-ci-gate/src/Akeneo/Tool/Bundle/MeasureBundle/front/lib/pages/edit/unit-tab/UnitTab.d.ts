import React from 'react';
import { MeasurementFamily } from '../../../model/measurement-family';
import { UnitCode } from '../../../model/unit';
import { ValidationError } from '@akeneo-pim-community/shared';
declare type UnitTabProps = {
    measurementFamily: MeasurementFamily;
    errors: ValidationError[];
    onMeasurementFamilyChange: (measurementFamily: MeasurementFamily) => void;
    selectedUnitCode: UnitCode;
    selectUnitCode: (unitCode: UnitCode) => void;
};
declare const UnitTab: ({ measurementFamily, errors, onMeasurementFamilyChange, selectedUnitCode, selectUnitCode, }: UnitTabProps) => React.JSX.Element;
export { UnitTab };
