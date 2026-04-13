import {
    attributeDefaultValues,
    buildTreeNodeFromCategoryTree,
    CategoryTreeModel,
    convertToCategoryTree,
    isCategoryImageAttributeValueData,
    RICH_TEXT_DEFAULT_VALUE,
} from './Category';

describe('RICH_TEXT_DEFAULT_VALUE', () => {
    it("is '<p></p>\\n'", () => {
        expect(RICH_TEXT_DEFAULT_VALUE).toBe('<p></p>\n');
    });
});

describe('attributeDefaultValues', () => {
    it('text default is empty string', () => expect(attributeDefaultValues.text).toBe(''));
    it('textarea default is empty string', () => expect(attributeDefaultValues.textarea).toBe(''));
    it('richtext default is the rich text default value', () => {
        expect(attributeDefaultValues.richtext).toBe(RICH_TEXT_DEFAULT_VALUE);
    });
    it('image default is null', () => expect(attributeDefaultValues.image).toBeNull());
});

describe('isCategoryImageAttributeValueData', () => {
    it('returns true for null (empty image slot)', () => {
        expect(isCategoryImageAttributeValueData(null)).toBe(true);
    });

    it('returns true for a valid image file info object', () => {
        expect(
            isCategoryImageAttributeValueData({file_path: '/path/to/img.jpg', original_filename: 'img.jpg'})
        ).toBe(true);
    });

    it('returns false for a plain string (text attribute value)', () => {
        expect(isCategoryImageAttributeValueData('some text')).toBe(false);
    });

    it('returns false for an empty string', () => {
        expect(isCategoryImageAttributeValueData('')).toBe(false);
    });
});

describe('convertToCategoryTree', () => {
    it('parses id by stripping the "node_" prefix', () => {
        const tree = {
            attr: {id: 'node_42', 'data-code': 'shoes'},
            data: 'Shoes',
            state: 'closed' as const,
            children: [],
        };
        expect(convertToCategoryTree(tree).id).toBe(42);
    });

    it('maps code, label from attr and data fields', () => {
        const tree = {
            attr: {id: 'node_1', 'data-code': 'master'},
            data: 'Master catalog',
            state: 'closed jstree-root' as const,
        };
        const result = convertToCategoryTree(tree);
        expect(result.code).toBe('master');
        expect(result.label).toBe('Master catalog');
    });

    it('sets isRoot=true when state contains "root"', () => {
        const tree = {
            attr: {id: 'node_1', 'data-code': 'root'},
            data: 'Root',
            state: 'closed jstree-root' as const,
        };
        expect(convertToCategoryTree(tree).isRoot).toBe(true);
    });

    it('sets isRoot=false when state does not contain "root"', () => {
        const tree = {
            attr: {id: 'node_1', 'data-code': 'child'},
            data: 'Child',
            state: 'closed' as const,
        };
        expect(convertToCategoryTree(tree).isRoot).toBe(false);
    });

    it('sets isLeaf=true when state is "leaf"', () => {
        const tree = {
            attr: {id: 'node_5', 'data-code': 'leaf-cat'},
            data: 'Leaf',
            state: 'leaf' as const,
        };
        expect(convertToCategoryTree(tree).isLeaf).toBe(true);
    });

    it('recursively converts children', () => {
        const tree = {
            attr: {id: 'node_1', 'data-code': 'parent'},
            data: 'Parent',
            state: 'closed' as const,
            children: [
                {
                    attr: {id: 'node_2', 'data-code': 'child'},
                    data: 'Child',
                    state: 'leaf' as const,
                },
            ],
        };
        const result = convertToCategoryTree(tree);
        expect(result.children).toHaveLength(1);
        expect(result.children![0].id).toBe(2);
        expect(result.children![0].isLeaf).toBe(true);
    });

    it('returns empty children array when children is undefined', () => {
        const tree = {
            attr: {id: 'node_3', 'data-code': 'alone'},
            data: 'Alone',
            state: 'leaf' as const,
        };
        expect(convertToCategoryTree(tree).children).toStrictEqual([]);
    });
});

describe('buildTreeNodeFromCategoryTree', () => {
    const leafCategory: CategoryTreeModel = {
        id: 10,
        code: 'shoes',
        label: 'Shoes',
        isRoot: false,
        isLeaf: true,
        children: [],
    };

    it('sets identifier and label from the category', () => {
        const node = buildTreeNodeFromCategoryTree(leafCategory);
        expect(node.identifier).toBe(10);
        expect(node.label).toBe('Shoes');
        expect(node.code).toBe('shoes');
    });

    it('sets type="leaf" for a leaf category', () => {
        expect(buildTreeNodeFromCategoryTree(leafCategory).type).toBe('leaf');
    });

    it('sets type="root" for a root category', () => {
        const root: CategoryTreeModel = {...leafCategory, isRoot: true, isLeaf: false};
        expect(buildTreeNodeFromCategoryTree(root).type).toBe('root');
    });

    it('sets type="node" for a non-root non-leaf category', () => {
        const node: CategoryTreeModel = {...leafCategory, isRoot: false, isLeaf: false};
        expect(buildTreeNodeFromCategoryTree(node).type).toBe('node');
    });

    it('sets parentId from second argument (defaults to null)', () => {
        expect(buildTreeNodeFromCategoryTree(leafCategory).parentId).toBeNull();
        expect(buildTreeNodeFromCategoryTree(leafCategory, 5).parentId).toBe(5);
    });

    it('maps childrenIds from children array', () => {
        const parent: CategoryTreeModel = {
            ...leafCategory,
            isLeaf: false,
            children: [
                {...leafCategory, id: 11},
                {...leafCategory, id: 12},
            ],
        };
        expect(buildTreeNodeFromCategoryTree(parent).childrenIds).toStrictEqual([11, 12]);
    });

    it('sets childrenStatus="loaded" when children are present', () => {
        const parent: CategoryTreeModel = {
            ...leafCategory,
            isLeaf: false,
            children: [{...leafCategory, id: 11}],
        };
        expect(buildTreeNodeFromCategoryTree(parent).childrenStatus).toBe('loaded');
    });

    it('sets childrenStatus="idle" when children array is empty', () => {
        expect(buildTreeNodeFromCategoryTree(leafCategory).childrenStatus).toBe('idle');
    });

    it('embeds the original CategoryTreeModel in the data field', () => {
        const node = buildTreeNodeFromCategoryTree(leafCategory);
        expect(node.data).toBe(leafCategory);
    });
});
