import React from 'react';
import { MeasurementFamily } from '../../model/measurement-family';
import { Direction } from '../../model/direction';
declare type MeasurementFamilyTableProps = {
    measurementFamilies: MeasurementFamily[];
    toggleSortDirection: (columnCode: string) => void;
    getSortDirection: (columnCode: string) => Direction;
};
declare const MeasurementFamilyTable: ({ measurementFamilies, toggleSortDirection, getSortDirection, }: MeasurementFamilyTableProps) => React.JSX.Element;
export { MeasurementFamilyTable };
