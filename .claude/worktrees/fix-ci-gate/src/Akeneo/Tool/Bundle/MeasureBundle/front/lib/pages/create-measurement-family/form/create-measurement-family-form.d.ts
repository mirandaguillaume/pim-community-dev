import { LocaleCode } from '@akeneo-pim-community/shared';
import { MeasurementFamily } from '../../../model/measurement-family';
declare type CreateMeasurementFamilyForm = {
    family_code: string;
    family_label: string;
    standard_unit_code: string;
    standard_unit_label: string;
    standard_unit_symbol: string;
};
declare const initializeCreateMeasurementFamilyForm: () => Readonly<{
    family_code: string;
    family_label: string;
    standard_unit_code: string;
    standard_unit_label: string;
    standard_unit_symbol: string;
}>;
declare const createMeasurementFamilyFromForm: (data: CreateMeasurementFamilyForm, locale: LocaleCode) => MeasurementFamily;
export { initializeCreateMeasurementFamilyForm, createMeasurementFamilyFromForm };
export type { CreateMeasurementFamilyForm };
