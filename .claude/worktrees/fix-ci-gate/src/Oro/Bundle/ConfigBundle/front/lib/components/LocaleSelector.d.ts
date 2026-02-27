import { LocaleCode } from '@akeneo-pim-community/shared';
import React from 'react';
interface Props {
    value: LocaleCode;
    onChange: (value: LocaleCode) => void;
}
export declare function LocaleSelector(props: Props): React.JSX.Element;
export {};
