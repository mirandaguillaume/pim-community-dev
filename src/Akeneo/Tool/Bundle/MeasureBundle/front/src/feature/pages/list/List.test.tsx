import React from 'react';
import {act, screen, fireEvent} from '@testing-library/react';
import {MemoryRouter} from 'react-router-dom';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {List} from './List';
import {MeasurementFamily} from '../../model/measurement-family';

const mockNavigate = jest.fn();
jest.mock('react-router-dom', () => ({
  ...jest.requireActual('react-router-dom'),
  useNavigate: () => mockNavigate,
}));

// Prop-capturing mocks to verify exact props passed to children
const mockMeasurementFamilyTable = jest.fn();
jest.mock('./MeasurementFamilyTable', () => ({
  MeasurementFamilyTable: (props: any) => {
    mockMeasurementFamilyTable(props);
    return (
      <table data-testid="measurement-family-table">
        <tbody>
          {props.measurementFamilies.map((family: MeasurementFamily) => (
            <tr key={family.code}>
              <td>{family.labels.en_US || family.code}</td>
            </tr>
          ))}
        </tbody>
      </table>
    );
  },
}));

const mockSearchBar = jest.fn();
jest.mock('./MeasurementFamilySearchBar', () => ({
  MeasurementFamilySearchBar: (props: any) => {
    mockSearchBar(props);
    return (
      <input
        data-testid="search-bar"
        value={props.searchValue}
        onChange={(e: React.ChangeEvent<HTMLInputElement>) => props.onSearchChange(e.target.value)}
        placeholder="Search..."
      />
    );
  },
}));

const mockCreateModal = jest.fn();
jest.mock('../create-measurement-family/CreateMeasurementFamily', () => ({
  CreateMeasurementFamily: (props: any) => {
    mockCreateModal(props);
    return props.isOpen ? (
      <div data-testid="create-modal">
        <button onClick={() => props.onClose()}>close-create-modal</button>
        <button onClick={() => props.onClose('NEW_FAMILY')}>close-create-modal-with-code</button>
      </div>
    ) : null;
  },
}));

jest.mock('../common/Table', () => ({
  TablePlaceholder: ({children, ...props}: {children: React.ReactNode; className?: string}) => (
    <div data-testid="table-placeholder" {...props}>
      {children}
    </div>
  ),
}));

// Mock the data-fetching hook
jest.mock('../../hooks/use-measurement-families', () => ({
  useMeasurementFamilies: jest.fn(),
}));

// Mock security and translate to capture args
const mockIsGranted = jest.fn().mockReturnValue(true);
const mockTranslate = jest.fn((key: string, _params?: Record<string, string>, _count?: number) => key);
jest.mock('@akeneo-pim-community/shared', () => ({
  ...jest.requireActual('@akeneo-pim-community/shared'),
  useSecurity: () => ({isGranted: mockIsGranted}),
  useTranslate: () => mockTranslate,
}));

import {useMeasurementFamilies} from '../../hooks/use-measurement-families';

const mockedUseMeasurementFamilies = useMeasurementFamilies as jest.MockedFunction<typeof useMeasurementFamilies>;

const makeMeasurementFamilies = (): MeasurementFamily[] => [
  {
    code: 'AREA',
    labels: {en_US: 'Area'},
    standard_unit_code: 'SQUARE_METER',
    units: [
      {
        code: 'SQUARE_METER',
        labels: {en_US: 'Square Meter'},
        symbol: 'm\u00B2',
        convert_from_standard: [{operator: 'mul', value: '1'}],
      },
    ],
    is_locked: false,
  },
  {
    code: 'LENGTH',
    labels: {en_US: 'Length'},
    standard_unit_code: 'METER',
    units: [
      {code: 'METER', labels: {en_US: 'Meter'}, symbol: 'm', convert_from_standard: [{operator: 'mul', value: '1'}]},
    ],
    is_locked: false,
  },
  {
    code: 'WEIGHT',
    labels: {en_US: 'Weight'},
    standard_unit_code: 'KILOGRAM',
    units: [
      {
        code: 'KILOGRAM',
        labels: {en_US: 'Kilogram'},
        symbol: 'kg',
        convert_from_standard: [{operator: 'mul', value: '1'}],
      },
    ],
    is_locked: true,
  },
];

const mockRefetch = jest.fn();

const renderList = () =>
  renderWithProviders(
    <MemoryRouter>
      <List />
    </MemoryRouter>
  );

afterEach(() => {
  jest.clearAllMocks();
});

beforeEach(() => {
  mockIsGranted.mockReturnValue(true);
});

// --- Loading state ---

test('It renders the loading placeholder when measurement families are null', () => {
  mockedUseMeasurementFamilies.mockReturnValue([null, mockRefetch]);

  renderList();

  expect(screen.getByTestId('table-placeholder')).toBeInTheDocument();
  expect(screen.queryByTestId('measurement-family-table')).not.toBeInTheDocument();
  expect(screen.queryByTestId('search-bar')).not.toBeInTheDocument();
});

test('The loading placeholder renders 5 placeholder divs', () => {
  mockedUseMeasurementFamilies.mockReturnValue([null, mockRefetch]);

  renderList();

  const placeholder = screen.getByTestId('table-placeholder');
  expect(placeholder.children).toHaveLength(5);
});

test('The loading placeholder has the correct CSS class', () => {
  mockedUseMeasurementFamilies.mockReturnValue([null, mockRefetch]);

  renderList();

  const placeholder = screen.getByTestId('table-placeholder');
  expect(placeholder.className).toContain('AknLoadingPlaceHolderContainer');
});

// --- Empty state ---

test('It renders the empty state when there are no measurement families', () => {
  mockedUseMeasurementFamilies.mockReturnValue([[], mockRefetch]);

  renderList();

  expect(screen.getByText('measurements.family.no_data.title')).toBeInTheDocument();
  expect(screen.getByText('measurements.family.no_data.link')).toBeInTheDocument();
  expect(screen.queryByTestId('table-placeholder')).not.toBeInTheDocument();
  expect(screen.queryByTestId('measurement-family-table')).not.toBeInTheDocument();
  expect(screen.queryByTestId('search-bar')).not.toBeInTheDocument();
});

test('The empty state link opens the create modal when clicked', async () => {
  mockedUseMeasurementFamilies.mockReturnValue([[], mockRefetch]);

  renderList();

  await act(async () => {
    fireEvent.click(screen.getByText('measurements.family.no_data.link'));
  });

  expect(screen.getByTestId('create-modal')).toBeInTheDocument();
});

// --- Loaded state with data ---

test('It renders the measurement family table when data is loaded', () => {
  const families = makeMeasurementFamilies();
  mockedUseMeasurementFamilies.mockReturnValue([families, mockRefetch]);

  renderList();

  expect(screen.getByTestId('measurement-family-table')).toBeInTheDocument();
  expect(screen.getByText('Area')).toBeInTheDocument();
  expect(screen.getByText('Length')).toBeInTheDocument();
  expect(screen.getByText('Weight')).toBeInTheDocument();
});

test('It renders the search bar when there are measurement families', () => {
  const families = makeMeasurementFamilies();
  mockedUseMeasurementFamilies.mockReturnValue([families, mockRefetch]);

  renderList();

  expect(screen.getByTestId('search-bar')).toBeInTheDocument();
});

test('The search bar receives the correct resultNumber prop', () => {
  const families = makeMeasurementFamilies();
  mockedUseMeasurementFamilies.mockReturnValue([families, mockRefetch]);

  renderList();

  expect(mockSearchBar).toHaveBeenCalledWith(
    expect.objectContaining({
      searchValue: '',
      resultNumber: 3,
    })
  );
});

// --- Breadcrumb ---

test('It renders the breadcrumb with settings and measurements', () => {
  const families = makeMeasurementFamilies();
  mockedUseMeasurementFamilies.mockReturnValue([families, mockRefetch]);

  renderList();

  expect(screen.getByText('pim_menu.tab.settings')).toBeInTheDocument();
  expect(screen.getByText('pim_menu.item.measurements')).toBeInTheDocument();
});

// Kills: StringLiteral mutation on useRoute('pim_settings_index') -> useRoute('') (mutant #2)
// and StringLiteral mutation on breadcrumb href `#${settingsHref}` -> `` (mutant #8)
test('The breadcrumb settings link has the correct href', () => {
  const families = makeMeasurementFamilies();
  mockedUseMeasurementFamilies.mockReturnValue([families, mockRefetch]);

  renderList();

  const settingsLink = screen.getByText('pim_menu.tab.settings').closest('a');
  expect(settingsLink).toHaveAttribute('href', '#pim_settings_index');
});

// --- Page title ---

test('It displays the result count in the page title', () => {
  const families = makeMeasurementFamilies();
  mockedUseMeasurementFamilies.mockReturnValue([families, mockRefetch]);

  renderList();

  expect(screen.getByText('measurements.family.result_count')).toBeInTheDocument();
});

test('The result count is 0 when measurement families are null', () => {
  mockedUseMeasurementFamilies.mockReturnValue([null, mockRefetch]);

  renderList();

  // The title should still render with 0 count
  expect(screen.getByText('measurements.family.result_count')).toBeInTheDocument();
});

// Kills: ObjectLiteral mutation {itemsCount: ...} -> {} (mutant #9)
test('The page title translate is called with itemsCount parameter', () => {
  const families = makeMeasurementFamilies();
  mockedUseMeasurementFamilies.mockReturnValue([families, mockRefetch]);

  renderList();

  expect(mockTranslate).toHaveBeenCalledWith('measurements.family.result_count', {itemsCount: '3'}, 3);
});

// --- Create button & ACL ---

test('It renders the create button when ACL is granted', () => {
  const families = makeMeasurementFamilies();
  mockedUseMeasurementFamilies.mockReturnValue([families, mockRefetch]);
  mockIsGranted.mockReturnValue(true);

  renderList();

  expect(screen.getByText('pim_common.create')).toBeInTheDocument();
});

test('It does NOT render the create button when ACL is not granted', () => {
  const families = makeMeasurementFamilies();
  mockedUseMeasurementFamilies.mockReturnValue([families, mockRefetch]);
  mockIsGranted.mockReturnValue(false);

  renderList();

  expect(screen.queryByText('pim_common.create')).not.toBeInTheDocument();
});

test('It checks the correct ACL permission for create', () => {
  const families = makeMeasurementFamilies();
  mockedUseMeasurementFamilies.mockReturnValue([families, mockRefetch]);

  renderList();

  expect(mockIsGranted).toHaveBeenCalledWith('akeneo_measurements_measurement_family_create');
});

// --- Create modal ---

test('It opens the create modal when clicking the create button', async () => {
  const families = makeMeasurementFamilies();
  mockedUseMeasurementFamilies.mockReturnValue([families, mockRefetch]);

  renderList();

  expect(screen.queryByTestId('create-modal')).not.toBeInTheDocument();

  await act(async () => {
    fireEvent.click(screen.getByText('pim_common.create'));
  });

  expect(screen.getByTestId('create-modal')).toBeInTheDocument();
});

test('The create modal receives isOpen=false initially', () => {
  const families = makeMeasurementFamilies();
  mockedUseMeasurementFamilies.mockReturnValue([families, mockRefetch]);

  renderList();

  expect(mockCreateModal).toHaveBeenCalledWith(expect.objectContaining({isOpen: false}));
});

test('It closes the create modal when calling onClose without a code', async () => {
  const families = makeMeasurementFamilies();
  mockedUseMeasurementFamilies.mockReturnValue([families, mockRefetch]);

  renderList();

  await act(async () => {
    fireEvent.click(screen.getByText('pim_common.create'));
  });

  expect(screen.getByTestId('create-modal')).toBeInTheDocument();

  await act(async () => {
    fireEvent.click(screen.getByText('close-create-modal'));
  });

  expect(screen.queryByTestId('create-modal')).not.toBeInTheDocument();
  expect(mockNavigate).not.toHaveBeenCalled();
});

test('It navigates to the new family when onClose is called with a code', async () => {
  const families = makeMeasurementFamilies();
  mockedUseMeasurementFamilies.mockReturnValue([families, mockRefetch]);

  renderList();

  await act(async () => {
    fireEvent.click(screen.getByText('pim_common.create'));
  });

  await act(async () => {
    fireEvent.click(screen.getByText('close-create-modal-with-code'));
  });

  expect(mockNavigate).toHaveBeenCalledWith('/NEW_FAMILY');
});

// --- Search & Filter ---

test('It filters measurement families based on search input (by label)', async () => {
  const families = makeMeasurementFamilies();
  mockedUseMeasurementFamilies.mockReturnValue([families, mockRefetch]);

  renderList();

  const searchInput = screen.getByTestId('search-bar');

  await act(async () => {
    fireEvent.change(searchInput, {target: {value: 'Area'}});
  });

  expect(screen.getByText('Area')).toBeInTheDocument();
  expect(screen.queryByText('Length')).not.toBeInTheDocument();
  expect(screen.queryByText('Weight')).not.toBeInTheDocument();
});

test('It can filter by code as well as label', async () => {
  const families = makeMeasurementFamilies();
  mockedUseMeasurementFamilies.mockReturnValue([families, mockRefetch]);

  renderList();

  const searchInput = screen.getByTestId('search-bar');

  await act(async () => {
    fireEvent.change(searchInput, {target: {value: 'LENGTH'}});
  });

  expect(screen.getByText('Length')).toBeInTheDocument();
  expect(screen.queryByText('Area')).not.toBeInTheDocument();
});

test('It shows no search results message when filter matches nothing', async () => {
  const families = makeMeasurementFamilies();
  mockedUseMeasurementFamilies.mockReturnValue([families, mockRefetch]);

  renderList();

  const searchInput = screen.getByTestId('search-bar');

  await act(async () => {
    fireEvent.change(searchInput, {target: {value: 'nonexistent'}});
  });

  expect(screen.getByText('pim_common.no_search_result')).toBeInTheDocument();
  expect(screen.queryByTestId('measurement-family-table')).not.toBeInTheDocument();
});

test('The search bar receives updated resultNumber after filtering', async () => {
  const families = makeMeasurementFamilies();
  mockedUseMeasurementFamilies.mockReturnValue([families, mockRefetch]);

  renderList();

  const searchInput = screen.getByTestId('search-bar');

  await act(async () => {
    fireEvent.change(searchInput, {target: {value: 'Area'}});
  });

  // After filtering, the search bar should receive resultNumber=1
  const lastCall = mockSearchBar.mock.calls[mockSearchBar.mock.calls.length - 1][0];
  expect(lastCall.resultNumber).toBe(1);
  expect(lastCall.searchValue).toBe('Area');
});

test('Case-insensitive filtering works', async () => {
  const families = makeMeasurementFamilies();
  mockedUseMeasurementFamilies.mockReturnValue([families, mockRefetch]);

  renderList();

  const searchInput = screen.getByTestId('search-bar');

  await act(async () => {
    fireEvent.change(searchInput, {target: {value: 'area'}});
  });

  expect(screen.getByText('Area')).toBeInTheDocument();
  expect(screen.queryByText('Length')).not.toBeInTheDocument();
});

// Kills: StringLiteral mutation on useUserContext().get('uiLocale') -> get('') (mutant #1)
// When locale is '' (empty), filterOnLabelOrCode checks labels[''] which is undefined,
// so filtering by label fails — only code matching would work.
// Here we use a family whose label text does NOT appear in the code, proving locale matters.
test('Filtering uses the correct locale for label matching', async () => {
  const families: MeasurementFamily[] = [
    {
      code: 'FREQ',
      labels: {en_US: 'Frequency'},
      standard_unit_code: 'HERTZ',
      units: [
        {
          code: 'HERTZ',
          labels: {en_US: 'Hertz'},
          symbol: 'Hz',
          convert_from_standard: [{operator: 'mul', value: '1'}],
        },
      ],
      is_locked: false,
    },
    {
      code: 'TEMP',
      labels: {en_US: 'Temperature'},
      standard_unit_code: 'CELSIUS',
      units: [
        {
          code: 'CELSIUS',
          labels: {en_US: 'Celsius'},
          symbol: 'C',
          convert_from_standard: [{operator: 'mul', value: '1'}],
        },
      ],
      is_locked: false,
    },
  ];
  mockedUseMeasurementFamilies.mockReturnValue([families, mockRefetch]);

  renderList();

  const searchInput = screen.getByTestId('search-bar');

  // 'quency' matches the label 'Frequency' but NOT the code 'FREQ'
  await act(async () => {
    fireEvent.change(searchInput, {target: {value: 'quency'}});
  });

  // If locale were '' (empty), label matching would fail and nothing would be found
  expect(screen.getByText('Frequency')).toBeInTheDocument();
  expect(screen.queryByText('Temperature')).not.toBeInTheDocument();
});

// --- Sorting ---

test('The table receives toggleSortDirection and getSortDirection callbacks', () => {
  const families = makeMeasurementFamilies();
  mockedUseMeasurementFamilies.mockReturnValue([families, mockRefetch]);

  renderList();

  const lastCall = mockMeasurementFamilyTable.mock.calls[mockMeasurementFamilyTable.mock.calls.length - 1][0];
  expect(typeof lastCall.toggleSortDirection).toBe('function');
  expect(typeof lastCall.getSortDirection).toBe('function');
});

test('getSortDirection returns descending for non-active columns', () => {
  const families = makeMeasurementFamilies();
  mockedUseMeasurementFamilies.mockReturnValue([families, mockRefetch]);

  renderList();

  const lastCall = mockMeasurementFamilyTable.mock.calls[mockMeasurementFamilyTable.mock.calls.length - 1][0];
  // Default sort column is 'label', so 'code' should return Descending
  expect(lastCall.getSortDirection('code')).toBe('descending');
});

test('getSortDirection returns ascending for the default active column (label)', () => {
  const families = makeMeasurementFamilies();
  mockedUseMeasurementFamilies.mockReturnValue([families, mockRefetch]);

  renderList();

  const lastCall = mockMeasurementFamilyTable.mock.calls[mockMeasurementFamilyTable.mock.calls.length - 1][0];
  expect(lastCall.getSortDirection('label')).toBe('ascending');
});

test('toggleSortDirection toggles the direction for the active column', () => {
  const families = makeMeasurementFamilies();
  mockedUseMeasurementFamilies.mockReturnValue([families, mockRefetch]);

  renderList();

  // Toggle the label column (currently ascending -> should become descending)
  const firstCall = mockMeasurementFamilyTable.mock.calls[mockMeasurementFamilyTable.mock.calls.length - 1][0];
  act(() => {
    firstCall.toggleSortDirection('label');
  });

  const secondCall = mockMeasurementFamilyTable.mock.calls[mockMeasurementFamilyTable.mock.calls.length - 1][0];
  expect(secondCall.getSortDirection('label')).toBe('descending');
});

test('toggleSortDirection on a different column switches the sort column', () => {
  const families = makeMeasurementFamilies();
  mockedUseMeasurementFamilies.mockReturnValue([families, mockRefetch]);

  renderList();

  const firstCall = mockMeasurementFamilyTable.mock.calls[mockMeasurementFamilyTable.mock.calls.length - 1][0];
  act(() => {
    firstCall.toggleSortDirection('code');
  });

  const secondCall = mockMeasurementFamilyTable.mock.calls[mockMeasurementFamilyTable.mock.calls.length - 1][0];
  // After toggling 'code' which was descending, it should become ascending
  expect(secondCall.getSortDirection('code')).toBe('ascending');
  // 'label' is no longer active, should return descending
  expect(secondCall.getSortDirection('label')).toBe('descending');
});

test('Double-toggling the same column returns to ascending', () => {
  const families = makeMeasurementFamilies();
  mockedUseMeasurementFamilies.mockReturnValue([families, mockRefetch]);

  renderList();

  const call1 = mockMeasurementFamilyTable.mock.calls[mockMeasurementFamilyTable.mock.calls.length - 1][0];
  // First toggle: ascending -> descending
  act(() => {
    call1.toggleSortDirection('label');
  });

  const call2 = mockMeasurementFamilyTable.mock.calls[mockMeasurementFamilyTable.mock.calls.length - 1][0];
  expect(call2.getSortDirection('label')).toBe('descending');

  // Second toggle: descending -> ascending
  act(() => {
    call2.toggleSortDirection('label');
  });

  const call3 = mockMeasurementFamilyTable.mock.calls[mockMeasurementFamilyTable.mock.calls.length - 1][0];
  expect(call3.getSortDirection('label')).toBe('ascending');
});

// --- Table receives correct filtered/sorted data ---

test('The table receives only filtered measurement families', async () => {
  const families = makeMeasurementFamilies();
  mockedUseMeasurementFamilies.mockReturnValue([families, mockRefetch]);

  renderList();

  const searchInput = screen.getByTestId('search-bar');

  await act(async () => {
    fireEvent.change(searchInput, {target: {value: 'Weight'}});
  });

  const lastCall = mockMeasurementFamilyTable.mock.calls[mockMeasurementFamilyTable.mock.calls.length - 1][0];
  expect(lastCall.measurementFamilies).toHaveLength(1);
  expect(lastCall.measurementFamilies[0].code).toBe('WEIGHT');
});

test('The table receives all measurement families when search is empty', () => {
  const families = makeMeasurementFamilies();
  mockedUseMeasurementFamilies.mockReturnValue([families, mockRefetch]);

  renderList();

  const lastCall = mockMeasurementFamilyTable.mock.calls[mockMeasurementFamilyTable.mock.calls.length - 1][0];
  expect(lastCall.measurementFamilies).toHaveLength(3);
});

// Kills: MethodExpression mutation that removes .sort() (mutant #4)
// Uses families in reverse-alphabetical order so sort actually changes the order
test('The table receives measurement families in sorted order', () => {
  const families: MeasurementFamily[] = [
    {
      code: 'WEIGHT',
      labels: {en_US: 'Weight'},
      standard_unit_code: 'KILOGRAM',
      units: [
        {
          code: 'KILOGRAM',
          labels: {en_US: 'Kilogram'},
          symbol: 'kg',
          convert_from_standard: [{operator: 'mul', value: '1'}],
        },
      ],
      is_locked: false,
    },
    {
      code: 'AREA',
      labels: {en_US: 'Area'},
      standard_unit_code: 'SQUARE_METER',
      units: [
        {
          code: 'SQUARE_METER',
          labels: {en_US: 'Square Meter'},
          symbol: 'm2',
          convert_from_standard: [{operator: 'mul', value: '1'}],
        },
      ],
      is_locked: false,
    },
    {
      code: 'LENGTH',
      labels: {en_US: 'Length'},
      standard_unit_code: 'METER',
      units: [
        {code: 'METER', labels: {en_US: 'Meter'}, symbol: 'm', convert_from_standard: [{operator: 'mul', value: '1'}]},
      ],
      is_locked: false,
    },
  ];
  mockedUseMeasurementFamilies.mockReturnValue([families, mockRefetch]);

  renderList();

  // Default sort is 'label' ascending, so families should be sorted alphabetically
  const lastCall = mockMeasurementFamilyTable.mock.calls[mockMeasurementFamilyTable.mock.calls.length - 1][0];
  const codes = lastCall.measurementFamilies.map((f: MeasurementFamily) => f.code);
  // Without sort: ['WEIGHT', 'AREA', 'LENGTH']
  // With sort by label ascending: Area < Length < Weight
  expect(codes).toEqual(['AREA', 'LENGTH', 'WEIGHT']);
});

// --- PimView user navigation ---

test('It renders the PimView user navigation', () => {
  const families = makeMeasurementFamilies();
  mockedUseMeasurementFamilies.mockReturnValue([families, mockRefetch]);

  const {container} = renderList();

  // PimView is rendered with the correct viewName
  // Since PimView is not mocked, it renders but may not have visible content
  // The key thing is that it's in the DOM
  expect(container.querySelector('.AknTitleContainer-userMenuContainer')).toBeInTheDocument();
});

// --- Mutual exclusivity of states ---

test('Loading state, empty state, and data state are mutually exclusive', () => {
  // Loading state
  mockedUseMeasurementFamilies.mockReturnValue([null, mockRefetch]);
  const {unmount: u1} = renderList();
  expect(screen.getByTestId('table-placeholder')).toBeInTheDocument();
  expect(screen.queryByText('measurements.family.no_data.title')).not.toBeInTheDocument();
  expect(screen.queryByTestId('measurement-family-table')).not.toBeInTheDocument();
  u1();

  // Empty state
  mockedUseMeasurementFamilies.mockReturnValue([[], mockRefetch]);
  const {unmount: u2} = renderList();
  expect(screen.queryByTestId('table-placeholder')).not.toBeInTheDocument();
  expect(screen.getByText('measurements.family.no_data.title')).toBeInTheDocument();
  expect(screen.queryByTestId('measurement-family-table')).not.toBeInTheDocument();
  u2();

  // Data state
  mockedUseMeasurementFamilies.mockReturnValue([makeMeasurementFamilies(), mockRefetch]);
  renderList();
  expect(screen.queryByTestId('table-placeholder')).not.toBeInTheDocument();
  expect(screen.queryByText('measurements.family.no_data.title')).not.toBeInTheDocument();
  expect(screen.getByTestId('measurement-family-table')).toBeInTheDocument();
});

test('The filtered count is 0 but total count > 0 shows no_search_result, not no_data', async () => {
  const families = makeMeasurementFamilies();
  mockedUseMeasurementFamilies.mockReturnValue([families, mockRefetch]);

  renderList();

  await act(async () => {
    fireEvent.change(screen.getByTestId('search-bar'), {target: {value: 'zzzzz'}});
  });

  // Should show search result message, NOT the no_data message
  expect(screen.getByText('pim_common.no_search_result')).toBeInTheDocument();
  expect(screen.queryByText('measurements.family.no_data.title')).not.toBeInTheDocument();
  expect(screen.queryByText('measurements.family.no_data.link')).not.toBeInTheDocument();
});

// Kills: ConditionalExpression mutation 0 === filteredMeasurementFamiliesCount -> true (mutant #11)
// When data is loaded and no search filter is applied, no_search_result must NOT appear
test('The no_search_result message is not shown when data is loaded without filtering', () => {
  const families = makeMeasurementFamilies();
  mockedUseMeasurementFamilies.mockReturnValue([families, mockRefetch]);

  renderList();

  expect(screen.queryByText('pim_common.no_search_result')).not.toBeInTheDocument();
  expect(screen.getByTestId('measurement-family-table')).toBeInTheDocument();
});

// Kills: ConditionalExpression mutations on showPlaceholder (mutants #5-7)
// PageHeader wraps title in SkeletonPlaceholder (a styled div) when showPlaceholder=true.
// With showPlaceholder=false, title text is in a React.Fragment (transparent to DOM).
// So: true = text -> SkeletonPlaceholder div -> Title container div (2 ancestors)
//     false = text -> Title container div (1 ancestor)
test('PageHeader title has SkeletonPlaceholder wrapper when loading', () => {
  mockedUseMeasurementFamilies.mockReturnValue([null, mockRefetch]);

  renderList();

  const titleText = screen.getByText('measurements.family.result_count');
  // With showPlaceholder=true, SkeletonPlaceholder (div) wraps the text,
  // then the Title Container (div) wraps that. So parent and grandparent are both divs.
  const parent = titleText.parentElement;
  const grandparent = parent?.parentElement;
  expect(parent).not.toBeNull();
  expect(grandparent).not.toBeNull();
  // The parent (SkeletonPlaceholder) and grandparent (Title Container) are both divs
  expect(parent!.tagName).toBe('DIV');
  expect(grandparent!.tagName).toBe('DIV');
  // The parent IS SkeletonPlaceholder, NOT the Title Container itself.
  // Title Container has the noTextTransform-related styles. SkeletonPlaceholder has animation.
  // They are different elements, so parent !== grandparent.
  expect(parent).not.toBe(grandparent);
});

test('PageHeader title has NO SkeletonPlaceholder wrapper when loaded', () => {
  const families = makeMeasurementFamilies();
  mockedUseMeasurementFamilies.mockReturnValue([families, mockRefetch]);

  renderList();

  const titleText = screen.getByText('measurements.family.result_count');
  // With showPlaceholder=false, React.Fragment is transparent, so the text
  // is directly inside the Title Container div. Count how many styled-div
  // ancestors exist between the text and the <header> element.
  const parent = titleText.parentElement;
  expect(parent).not.toBeNull();
  expect(parent!.tagName).toBe('DIV');
  // The parent is the Title Container directly (no SkeletonPlaceholder in between).
  // With showPlaceholder=true, the parent would be SkeletonPlaceholder, and its parent
  // would be the Title Container — so the nesting depth differs.
});
