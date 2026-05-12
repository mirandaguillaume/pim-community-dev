import {
  redirectToAttributeGridFilteredByFamilyAndQuality,
  redirectToAttributeGridFilteredByFamilyAndQualityAndSelectAttributeTypes,
  redirectToFilteredAttributeGrid,
} from '../../../../../front/src/infrastructure/navigation/AttributeGridRouter';

jest.mock('pim/router', () => ({generate: (route: string) => `/route/${route}`}));
jest.mock('pim/datagrid/state', () => ({set: jest.fn()}));

import DatagridState from 'pim/datagrid/state';

describe('AttributeGridRouter', () => {
  let originalHref: string;

  beforeEach(() => {
    originalHref = window.location.href;
    (DatagridState.set as jest.Mock).mockClear();
    Object.defineProperty(window, 'location', {
      value: {href: ''},
      writable: true,
    });
  });

  describe('redirectToFilteredAttributeGrid', () => {
    it('sets datagrid state with provided filters', () => {
      redirectToFilteredAttributeGrid('f[family][value][]=accessories');

      expect(DatagridState.set).toHaveBeenCalledWith('attribute-grid', {
        filters: 'f[family][value][]=accessories',
      });
    });

    it('sets window.location.href to the generated route fragment', () => {
      redirectToFilteredAttributeGrid('f[quality][value]=to_improve');

      expect(window.location.href).toBe('#/route/pim_enrich_attribute_index');
    });
  });

  describe('redirectToAttributeGridFilteredByFamilyAndQuality', () => {
    it('calls DatagridState.set with family and quality filter', () => {
      redirectToAttributeGridFilteredByFamilyAndQuality('accessories');

      const expectedFilters = `i=1&p=25&s[label]=-1&f[family][value][]=accessories&f[quality][value]=to_improve&t=attribute-grid`;

      expect(DatagridState.set).toHaveBeenCalledWith('attribute-grid', {
        filters: expectedFilters,
      });
    });
  });

  describe('redirectToAttributeGridFilteredByFamilyAndQualityAndSelectAttributeTypes', () => {
    it('calls DatagridState.set with family, quality and select attribute type filters', () => {
      redirectToAttributeGridFilteredByFamilyAndQualityAndSelectAttributeTypes('accessories');

      const expectedFilters =
        `i=1&p=25&s[label]=-1&f[family][value][]=accessories&f[quality][value]=to_improve&t=attribute-grid` +
        `&f[type][value][]=pim_catalog_multiselect&f[type][value][]=pim_catalog_simpleselect`;

      expect(DatagridState.set).toHaveBeenCalledWith('attribute-grid', {
        filters: expectedFilters,
      });
    });
  });
});
