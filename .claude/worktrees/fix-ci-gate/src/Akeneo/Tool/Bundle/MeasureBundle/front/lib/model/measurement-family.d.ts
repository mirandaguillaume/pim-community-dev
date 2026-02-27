import { LabelCollection, LocaleCode } from '@akeneo-pim-community/shared';
import { Direction } from './direction';
import { Operation } from './operation';
import { Unit, UnitCode } from './unit';
declare type MeasurementFamilyCode = string;
declare type MeasurementFamily = {
    code: MeasurementFamilyCode;
    labels: LabelCollection;
    standard_unit_code: string;
    units: Unit[];
    is_locked: boolean;
};
declare const getMeasurementFamilyLabel: (measurementFamily: MeasurementFamily, locale: LocaleCode) => string;
declare const setMeasurementFamilyLabel: (measurementFamily: MeasurementFamily, locale: LocaleCode, value: string) => MeasurementFamily;
declare const setUnitLabel: (measurementFamily: MeasurementFamily, unitCode: UnitCode, locale: LocaleCode, value: string) => MeasurementFamily;
declare const setUnitOperations: (measurementFamily: MeasurementFamily, unitCode: UnitCode, operations: Operation[]) => {
    units: Unit[];
    code: MeasurementFamilyCode;
    labels: LabelCollection;
    standard_unit_code: string;
    is_locked: boolean;
};
declare const addUnit: (measurementFamily: MeasurementFamily, unit: Unit) => MeasurementFamily;
declare const removeUnit: (measurementFamily: MeasurementFamily, unitCode: UnitCode) => MeasurementFamily;
declare const setUnitSymbol: (measurementFamily: MeasurementFamily, unitCode: UnitCode, value: string) => MeasurementFamily;
declare const getUnit: (measurementFamily: MeasurementFamily, unitCode: UnitCode) => Unit | undefined;
declare const getUnitIndex: (measurementFamily: MeasurementFamily, unitCode: UnitCode) => number;
declare const getStandardUnit: (measurementFamily: MeasurementFamily) => Unit;
declare const getStandardUnitLabel: (measurementFamily: MeasurementFamily, locale: LocaleCode) => string;
declare const filterOnLabelOrCode: (searchValue: string, locale: LocaleCode) => (entity: {
    code: string;
    labels: LabelCollection;
}) => boolean;
declare const sortMeasurementFamily: (sortDirection: Direction, locale: LocaleCode, sortColumn: string) => (first: MeasurementFamily, second: MeasurementFamily) => number;
export { getMeasurementFamilyLabel, setMeasurementFamilyLabel, getUnit, getUnitIndex, setUnitLabel, setUnitSymbol, setUnitOperations, addUnit, removeUnit, getStandardUnit, getStandardUnitLabel, filterOnLabelOrCode, sortMeasurementFamily, };
export type { MeasurementFamily, MeasurementFamilyCode };
