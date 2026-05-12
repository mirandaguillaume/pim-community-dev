import React from 'react';
import {screen} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/shared/lib/tests';
import {CategoryTree} from './CategoryTree';
import {useCategoryTreeNode} from '../../hooks/useCategoryTreeNode';
import {useDragTreeNode} from '../../hooks/useDragTreeNode';
import {useDropTreeNode} from '../../hooks/useDropTreeNode';
import {useCountProductsBeforeDeleteCategory} from '../../hooks/useCountProductsBeforeDeleteCategory';

jest.mock('../../hooks/useCategoryTreeNode');
jest.mock('../../hooks/useDragTreeNode');
jest.mock('../../hooks/useDropTreeNode');
jest.mock('../../hooks/useCountProductsBeforeDeleteCategory');
jest.mock('akeneo-design-system', () => ({
  ...jest.requireActual('akeneo-design-system'),
  SkeletonPlaceholder: () => <div data-testid="skeleton-placeholder" />,
}));

const mockedUseCategoryTreeNode = useCategoryTreeNode as jest.MockedFunction<typeof useCategoryTreeNode>;
const mockedUseDragTreeNode = useDragTreeNode as jest.MockedFunction<typeof useDragTreeNode>;
const mockedUseDropTreeNode = useDropTreeNode as jest.MockedFunction<typeof useDropTreeNode>;
const mockedUseCount = useCountProductsBeforeDeleteCategory as jest.MockedFunction<
  typeof useCountProductsBeforeDeleteCategory
>;

const rootModel = {
  id: 1,
  code: 'master',
  label: 'Master catalog',
  isRoot: true,
  isLeaf: false,
  children: [],
};

describe('CategoryTree', () => {
  beforeEach(() => jest.clearAllMocks());

  it('renders Tree.Skeleton (skeleton placeholders) when root is null', () => {
    renderWithProviders(<CategoryTree root={null} />);
    expect(screen.getAllByTestId('skeleton-placeholder').length).toBeGreaterThan(0);
  });

  it('renders the root node label when root is provided', () => {
    const node = {
      identifier: 1, label: 'Master catalog', code: 'master', parentId: null,
      childrenIds: [], data: rootModel, type: 'root' as const, childrenStatus: 'idle' as const,
    };
    mockedUseCategoryTreeNode.mockReturnValue({
      node, children: [], loadChildren: jest.fn(), moveTo: jest.fn(),
      onDeleteCategory: jest.fn(), onCreateCategory: jest.fn(),
      isOpen: true, open: jest.fn(), close: jest.fn(),
    } as any);
    mockedUseDragTreeNode.mockReturnValue({isDragged: jest.fn(() => false), isDraggable: false} as any);
    mockedUseDropTreeNode.mockReturnValue({placeholderPosition: 'none'} as any);
    mockedUseCount.mockReturnValue(jest.fn());

    renderWithProviders(<CategoryTree root={rootModel} />);
    expect(screen.getByText('Master catalog')).toBeInTheDocument();
  });
});
