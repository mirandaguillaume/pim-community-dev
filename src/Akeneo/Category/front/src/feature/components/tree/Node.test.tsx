import React from 'react';
import {render, screen} from '@testing-library/react';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {renderWithProviders} from '@akeneo-pim-community/shared/lib/tests';
import {Node} from './Node';
import {useCategoryTreeNode} from '../../hooks/useCategoryTreeNode';
import {useDragTreeNode} from '../../hooks/useDragTreeNode';
import {useDropTreeNode} from '../../hooks/useDropTreeNode';
import {useCountProductsBeforeDeleteCategory} from '../../hooks/useCountProductsBeforeDeleteCategory';

jest.mock('../../hooks/useCategoryTreeNode');
jest.mock('../../hooks/useDragTreeNode');
jest.mock('../../hooks/useDropTreeNode');
jest.mock('../../hooks/useCountProductsBeforeDeleteCategory');

const mockedUseCategoryTreeNode = useCategoryTreeNode as jest.MockedFunction<typeof useCategoryTreeNode>;
const mockedUseDragTreeNode = useDragTreeNode as jest.MockedFunction<typeof useDragTreeNode>;
const mockedUseDropTreeNode = useDropTreeNode as jest.MockedFunction<typeof useDropTreeNode>;
const mockedUseCount = useCountProductsBeforeDeleteCategory as jest.MockedFunction<
  typeof useCountProductsBeforeDeleteCategory
>;

const makeNode = (type: 'root' | 'node' | 'leaf' = 'node') => ({
  identifier: 1,
  label: 'Electronics',
  code: 'electronics',
  parentId: null,
  childrenIds: [],
  data: {id: 1, code: 'electronics', label: 'Electronics', isRoot: type === 'root', isLeaf: type === 'leaf'},
  type,
  childrenStatus: 'idle' as const,
});

const setupMocks = (type: 'root' | 'node' | 'leaf' = 'node') => {
  const node = makeNode(type);
  mockedUseCategoryTreeNode.mockReturnValue({
    node,
    children: [],
    loadChildren: jest.fn(),
    moveTo: jest.fn(),
    onDeleteCategory: jest.fn(),
    onCreateCategory: jest.fn(),
    isOpen: false,
    open: jest.fn(),
    close: jest.fn(),
  } as any);
  mockedUseDragTreeNode.mockReturnValue({isDragged: jest.fn(() => false), isDraggable: false} as any);
  mockedUseDropTreeNode.mockReturnValue({placeholderPosition: 'none'} as any);
  mockedUseCount.mockReturnValue(jest.fn());
};

const renderNode = (props: Partial<React.ComponentProps<typeof Node>> = {}) =>
  renderWithProviders(<Node id={1} label="Electronics" code="electronics" {...props} />);

describe('Node', () => {
  beforeEach(() => jest.clearAllMocks());

  it('renders null when useCategoryTreeNode returns undefined node', () => {
    mockedUseCategoryTreeNode.mockReturnValue({node: undefined} as any);
    mockedUseDragTreeNode.mockReturnValue({isDragged: jest.fn(() => false), isDraggable: false} as any);
    mockedUseDropTreeNode.mockReturnValue({placeholderPosition: 'none'} as any);
    mockedUseCount.mockReturnValue(jest.fn());
    const {container} = renderNode();
    expect(container).toBeEmptyDOMElement();
  });

  it('renders the label when the node exists', () => {
    setupMocks();
    renderNode();
    expect(screen.getByText('Electronics')).toBeInTheDocument();
  });

  it('shows the add category button when addCategory prop is provided', () => {
    setupMocks();
    renderNode({addCategory: jest.fn()});
    expect(screen.getByText('pim_enrich.entity.category.new_category')).toBeInTheDocument();
  });

  it('does not show the add category button when addCategory prop is not provided', () => {
    setupMocks();
    renderNode();
    expect(screen.queryByText('pim_enrich.entity.category.new_category')).not.toBeInTheDocument();
  });

  it('shows the delete button for non-root nodes when deleteCategory is provided', () => {
    setupMocks('node');
    renderNode({deleteCategory: jest.fn()});
    expect(screen.getByText('pim_common.delete')).toBeInTheDocument();
  });

  it('hides the delete button for root nodes', () => {
    setupMocks('root');
    renderNode({deleteCategory: jest.fn()});
    expect(screen.queryByText('pim_common.delete')).not.toBeInTheDocument();
  });
});
