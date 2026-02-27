import { MeasurementFamily } from '../model/measurement-family';
declare const useMeasurementFamilies: () => [MeasurementFamily[] | null, () => Promise<void>];
export { useMeasurementFamilies };
