import React from 'react';
declare type TypeFilterProps = {
    typeFilterValue: string[];
    onTypeFilterChange: (typeFilterValue: string[]) => void;
};
declare const TypeFilter: ({ typeFilterValue, onTypeFilterChange }: TypeFilterProps) => React.JSX.Element;
export { TypeFilter };
