import React from 'react';
import { Unit, UnitCode } from '../../../model/unit';
declare type UnitRowProps = {
    unit: Unit;
    isStandardUnit: boolean;
    isSelected?: boolean;
    isInvalid?: boolean;
    onRowSelected: (unitCode: UnitCode) => void;
};
declare const UnitRow: ({ unit, isStandardUnit, isSelected, isInvalid, onRowSelected }: UnitRowProps) => React.JSX.Element;
export { UnitRow };
