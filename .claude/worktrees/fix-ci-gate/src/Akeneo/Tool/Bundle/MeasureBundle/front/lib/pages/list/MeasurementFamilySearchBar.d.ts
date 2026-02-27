import React from 'react';
declare type MeasurementFamilySearchBarProps = {
    searchValue: string;
    onSearchChange: (searchValue: string) => void;
    resultNumber: number;
};
declare const MeasurementFamilySearchBar: ({ searchValue, onSearchChange, resultNumber }: MeasurementFamilySearchBarProps) => React.JSX.Element;
export { MeasurementFamilySearchBar };
