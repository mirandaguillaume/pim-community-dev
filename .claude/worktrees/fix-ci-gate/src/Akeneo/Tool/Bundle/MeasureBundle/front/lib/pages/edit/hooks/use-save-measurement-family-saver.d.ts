import { MeasurementFamily } from '../../../model/measurement-family';
import { ValidationError } from '@akeneo-pim-community/shared';
declare type SaverResult = {
    success: boolean;
    errors: ValidationError[];
};
declare type Saver = (measurementFamily: MeasurementFamily) => Promise<SaverResult>;
declare const useSaveMeasurementFamilySaver: () => Saver;
export { useSaveMeasurementFamilySaver };
