declare enum MeasurementFamilyRemoverResult {
    Success = "Success",
    Unprocessable = "Unprocessable",
    NotFound = "NotFound"
}
declare type Remover = (measurementFamilyCode: string) => Promise<MeasurementFamilyRemoverResult>;
declare const useMeasurementFamilyRemover: () => Remover;
export { useMeasurementFamilyRemover, MeasurementFamilyRemoverResult };
