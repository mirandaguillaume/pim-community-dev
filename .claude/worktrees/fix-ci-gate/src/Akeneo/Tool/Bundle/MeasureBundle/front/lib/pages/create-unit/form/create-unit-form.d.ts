import { ValidationError, Translate, LocaleCode } from '@akeneo-pim-community/shared';
import { Operation, Operator } from '../../../model/operation';
import { Unit } from '../../../model/unit';
import { MeasurementFamily } from '../../../model/measurement-family';
declare type CreateUnitForm = {
    code: string;
    label: string;
    symbol: string;
    operations: Operation[];
};
declare const initializeCreateUnitForm: () => Readonly<{
    code: string;
    label: string;
    symbol: string;
    operations: {
        operator: Operator;
        value: string;
    }[];
}>;
declare const createUnitFromForm: (data: CreateUnitForm, locale: LocaleCode) => Unit;
declare const validateCreateUnitForm: (data: CreateUnitForm, measurementFamily: MeasurementFamily, __: Translate) => ValidationError[];
export { initializeCreateUnitForm, createUnitFromForm, validateCreateUnitForm };
export type { CreateUnitForm };
