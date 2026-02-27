import React from 'react';
declare type UserFilterProps = {
    userFilterValue: string[];
    onUserFilterChange: (userFilterValue: string[]) => void;
};
declare const UserFilter: ({ userFilterValue, onUserFilterChange }: UserFilterProps) => React.JSX.Element;
export { UserFilter };
