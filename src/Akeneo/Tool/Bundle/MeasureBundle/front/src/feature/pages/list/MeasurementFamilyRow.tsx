import React from 'react';
import {useNavigate} from 'react-router-dom';
import {MeasurementFamily, getMeasurementFamilyLabel, getStandardUnitLabel} from '../../model/measurement-family';
import {useUserContext} from '@akeneo-pim-community/shared';
import {Table} from 'akeneo-design-system';

type MeasurementFamilyRowProps = {
  measurementFamily: MeasurementFamily;
};

const MeasurementFamilyRow = ({measurementFamily}: MeasurementFamilyRowProps) => {
  const locale = useUserContext().get('uiLocale');
  const navigate = useNavigate();
  const measurementFamilyLabel = getMeasurementFamilyLabel(measurementFamily, locale);

  return (
    <Table.Row onClick={() => navigate(`/${measurementFamily.code}`)}>
      <Table.Cell rowTitle={true}>{measurementFamilyLabel}</Table.Cell>
      <Table.Cell>{measurementFamily.code}</Table.Cell>
      <Table.Cell>{getStandardUnitLabel(measurementFamily, locale)}</Table.Cell>
      <Table.Cell>{measurementFamily.units.length}</Table.Cell>
    </Table.Row>
  );
};

export {MeasurementFamilyRow};
