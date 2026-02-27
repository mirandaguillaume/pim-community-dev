import { LocaleCode, LabelCollection } from '@akeneo-pim-community/shared';
import { Operation } from './operation';
declare type UnitCode = string;
declare type Unit = {
    code: UnitCode;
    labels: LabelCollection;
    symbol: string;
    convert_from_standard: Operation[];
};
declare const getUnitLabel: (unit: Unit, locale: LocaleCode) => string;
export { getUnitLabel };
export type { Unit, UnitCode };
