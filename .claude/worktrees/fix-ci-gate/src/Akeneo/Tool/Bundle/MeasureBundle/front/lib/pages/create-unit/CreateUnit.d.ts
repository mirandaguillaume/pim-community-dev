import React from 'react';
import { MeasurementFamily } from '../../model/measurement-family';
import { Unit } from '../../model/unit';
declare type CreateUnitProps = {
    measurementFamily: MeasurementFamily;
    onClose: () => void;
    onNewUnit: (unit: Unit) => void;
};
declare const CreateUnit: ({ onClose, onNewUnit, measurementFamily }: CreateUnitProps) => React.JSX.Element;
export { CreateUnit };
