import { MeasurementFamily, MeasurementFamilyCode } from '../model/measurement-family';
declare const useMeasurementFamily: (measurementFamilyCode: MeasurementFamilyCode) => [MeasurementFamily | null, (measurementFamily: MeasurementFamily) => void];
export { useMeasurementFamily };
