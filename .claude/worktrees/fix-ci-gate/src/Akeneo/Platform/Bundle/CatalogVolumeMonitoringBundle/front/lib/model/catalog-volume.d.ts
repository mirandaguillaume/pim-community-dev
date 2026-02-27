declare type CounterValue = number;
declare type AverageMaxValue = {
    average: number;
    max: number;
};
declare type CatalogVolume = {
    name: string;
    type: string;
    value: AverageMaxValue | CounterValue;
};
declare type Axis = {
    name: string;
    catalogVolumes: CatalogVolume[];
};
export type { CatalogVolume, Axis, AverageMaxValue, CounterValue };
