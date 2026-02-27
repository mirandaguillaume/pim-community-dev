import React from 'react';
declare type InnerTableProps = {
    content: {
        [key: string]: string | object;
    };
    className?: string;
};
declare const InnerTable: ({ content, ...rest }: InnerTableProps) => React.JSX.Element;
export { InnerTable };
