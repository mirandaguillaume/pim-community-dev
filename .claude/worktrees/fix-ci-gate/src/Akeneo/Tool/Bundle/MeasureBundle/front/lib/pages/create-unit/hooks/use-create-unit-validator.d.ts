import { ValidationError } from '@akeneo-pim-community/shared';
import { MeasurementFamilyCode } from '../../../model/measurement-family';
import { Unit } from '../../../model/unit';
declare type ValidatorResult = {
    valid: boolean;
    errors: ValidationError[];
};
declare type Validator = (measurementFamilyCode: MeasurementFamilyCode, data: Unit) => Promise<ValidatorResult>;
declare const useCreateUnitValidator: () => Validator;
export { useCreateUnitValidator };
