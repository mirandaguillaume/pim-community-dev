import {createWrapper} from '../../tests/hooks/config/createWrapper';
import {mockResponse} from '../../tests/test-utils';
import {useGetConditionItems} from '../useGetConditionItems';
import {act, renderHook, waitFor} from '@testing-library/react';
import {ATTRIBUTE_TYPE, CONDITION_NAMES, Conditions, Operator} from '../../models';

describe('useGetConditionItems', () => {
  test('it paginate items', async () => {
    const resultsPage1 = [
      {
        id: 'system',
        text: 'System',
        children: [
          {id: 'family', text: 'Family'},
          {id: 'enabled', text: 'Enabled'},
        ],
      },
      {id: 'marketing', text: 'Marketing', children: [{id: 'brand', text: 'Brand', type: ATTRIBUTE_TYPE.TEXT}]},
    ];

    const resultsPage2 = [
      {
        id: 'marketing',
        text: 'Marketing',
        children: [{id: 'color', text: 'Color', type: ATTRIBUTE_TYPE.SIMPLE_SELECT}],
      },
      {
        id: 'design',
        text: 'Design',
        children: [{id: 'main_color', text: 'Main Color', type: ATTRIBUTE_TYPE.MULTI_SELECT}],
      },
    ];

    const mergedResults = [
      {
        id: 'system',
        text: 'System',
        children: [
          {id: 'family', text: 'Family'},
          {id: 'enabled', text: 'Enabled'},
        ],
      },
      {
        id: 'marketing',
        text: 'Marketing',
        children: [
          {id: 'brand', text: 'Brand', type: ATTRIBUTE_TYPE.TEXT},
          {id: 'color', text: 'Color', type: ATTRIBUTE_TYPE.SIMPLE_SELECT},
        ],
      },
      {
        id: 'design',
        text: 'Design',
        children: [{id: 'main_color', text: 'Main Color', type: ATTRIBUTE_TYPE.MULTI_SELECT}],
      },
    ];

    const expectCallPage1 = mockResponse('akeneo_identifier_generator_get_conditions', 'GET', {
      ok: true,
      json: resultsPage1,
    });

    const conditions: Conditions = [];
    const {result} = renderHook(() => useGetConditionItems(true, conditions, 3), {wrapper: createWrapper()});

    await waitFor(() => {
      expect(result.current?.conditionItems?.length).toBeGreaterThan(0);
    });
    expectCallPage1();
    expect(result.current.conditionItems).toEqual(resultsPage1);

    const expectCallPage2 = mockResponse('akeneo_identifier_generator_get_conditions', 'GET', {
      ok: true,
      json: resultsPage2,
    });
    act(() => {
      result.current.handleNextPage();
    });
    await waitFor(() => {
      expect(result.current?.conditionItems?.length).toBeGreaterThan(2);
    });
    expectCallPage2();
    expect(result.current.conditionItems).toEqual(mergedResults);
  });

  test('it resets items on search', async () => {
    const resultsPage1 = [
      {
        id: 'system',
        text: 'System',
        children: [
          {id: 'family', text: 'Family'},
          {id: 'enabled', text: 'Enabled'},
        ],
      },
      {id: 'marketing', text: 'Marketing', children: [{id: 'brand', text: 'Brand'}]},
    ];

    const resultsWithSearch = [{id: 'system', text: 'System', children: [{id: 'family', text: 'Family'}]}];

    const expectCallPage1 = mockResponse('akeneo_identifier_generator_get_conditions', 'GET', {
      ok: true,
      json: resultsPage1,
    });

    const conditions: Conditions = [];
    const {result} = renderHook(() => useGetConditionItems(true, conditions, 3), {wrapper: createWrapper()});

    await waitFor(() => {
      expect(result.current?.conditionItems?.length).toBeGreaterThan(0);
    });
    expectCallPage1();
    expect(result.current.conditionItems).toEqual(resultsPage1);

    const expectCallPage2 = mockResponse('akeneo_identifier_generator_get_conditions', 'GET', {
      ok: true,
      json: resultsWithSearch,
    });
    act(() => {
      result.current.setSearchValue('fam');
    });
    act(() => {
      result.current.setSearchValue('family');
    });
    await waitFor(() => {
      expect(result.current?.conditionItems).toHaveLength(1);
    });
    expectCallPage2();
    expect(result.current.conditionItems).toEqual(resultsWithSearch);
  });

  test('it filters system items', async () => {
    const resultsPage1 = [
      {id: 'system', text: 'System', children: [{id: 'enabled', text: 'Enabled'}]},
      {id: 'marketing', text: 'Marketing', children: [{id: 'brand', text: 'Brand'}]},
    ];

    const expectCallPage1 = mockResponse('akeneo_identifier_generator_get_conditions', 'GET', {
      ok: true,
      json: resultsPage1,
    });

    const conditions: Conditions = [{type: CONDITION_NAMES.FAMILY, operator: Operator.EMPTY}];
    const {result} = renderHook(() => useGetConditionItems(true, conditions, 3), {wrapper: createWrapper()});

    await waitFor(() => {
      expect(result.current?.conditionItems?.length).toBeGreaterThan(0);
    });

    expectCallPage1();
    expect(result.current.conditionItems).toEqual(resultsPage1);
  });
});
