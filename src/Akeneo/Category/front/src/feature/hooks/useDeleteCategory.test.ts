import {renderHook, act} from '@testing-library/react';
import {useDeleteCategory} from './useDeleteCategory';
import {NotificationLevel, useNotify, useRouter, useTranslate} from '@akeneo-pim-community/shared';
import {deleteCategory} from '../infrastructure';

jest.mock('@akeneo-pim-community/shared', () => ({
  ...jest.requireActual('@akeneo-pim-community/shared'),
  useNotify: jest.fn(),
  useRouter: jest.fn(),
  useTranslate: jest.fn(),
}));
jest.mock('../infrastructure');

const mockedUseNotify = useNotify as jest.Mock;
const mockedUseRouter = useRouter as jest.Mock;
const mockedUseTranslate = useTranslate as jest.Mock;
const mockedDeleteCategory = deleteCategory as jest.Mock;

describe('useDeleteCategory', () => {
  let mockNotify: jest.Mock;
  let mockRouter: {generate: jest.Mock};
  let mockTranslate: jest.Mock;

  beforeEach(() => {
    mockNotify = jest.fn();
    mockRouter = {generate: jest.fn((route: string) => route)};
    mockTranslate = jest.fn((key: string) => key);

    mockedUseNotify.mockReturnValue(mockNotify);
    mockedUseRouter.mockReturnValue(mockRouter);
    mockedUseTranslate.mockReturnValue(mockTranslate);
    mockedDeleteCategory.mockResolvedValue({ok: true, errorMessage: ''});
  });

  describe('isCategoryDeletionPossible', () => {
    it('returns true when numberOfProducts is at the limit (100)', () => {
      const {result} = renderHook(() => useDeleteCategory());
      expect(result.current.isCategoryDeletionPossible('Master', 100)).toBe(true);
      expect(mockNotify).not.toHaveBeenCalled();
    });

    it('returns false and notifies when numberOfProducts exceeds the limit', () => {
      const {result} = renderHook(() => useDeleteCategory());
      const canDelete = result.current.isCategoryDeletionPossible('Master', 101);

      expect(canDelete).toBe(false);
      expect(mockNotify).toHaveBeenCalledWith(
        NotificationLevel.INFO,
        'pim_enrich.entity.category.category_deletion.products_limit_exceeded.title',
        'pim_enrich.entity.category.category_deletion.products_limit_exceeded.message'
      );
    });

    it('translates the exceeded message with label and limit', () => {
      const {result} = renderHook(() => useDeleteCategory());
      result.current.isCategoryDeletionPossible('Accessories', 200);

      expect(mockTranslate).toHaveBeenCalledWith(
        'pim_enrich.entity.category.category_deletion.products_limit_exceeded.message',
        {name: 'Accessories', limit: 100}
      );
    });
  });

  describe('handleDeleteCategory', () => {
    const baseCategory = {identifier: 42, label: 'Accessories', code: 'accessories', numberOfProducts: 5, onDelete: jest.fn()};

    it('calls onDelete and notifies success when response is ok', async () => {
      mockedDeleteCategory.mockResolvedValue({ok: true, errorMessage: ''});
      const onDelete = jest.fn();
      const {result} = renderHook(() => useDeleteCategory());

      await act(async () => {
        await result.current.handleDeleteCategory({...baseCategory, onDelete});
      });

      expect(onDelete).toHaveBeenCalled();
      expect(mockNotify).toHaveBeenCalledWith(
        NotificationLevel.SUCCESS,
        'pim_enrich.entity.category.category_deletion.success'
      );
    });

    it('does not call onDelete and notifies error when response is not ok', async () => {
      mockedDeleteCategory.mockResolvedValue({ok: false, errorMessage: 'Server error'});
      const onDelete = jest.fn();
      const {result} = renderHook(() => useDeleteCategory());

      await act(async () => {
        await result.current.handleDeleteCategory({...baseCategory, onDelete});
      });

      expect(onDelete).not.toHaveBeenCalled();
      expect(mockNotify).toHaveBeenCalledWith(NotificationLevel.ERROR, 'Server error');
    });

    it('uses generic error translation when errorMessage is empty', async () => {
      mockedDeleteCategory.mockResolvedValue({ok: false, errorMessage: ''});
      const {result} = renderHook(() => useDeleteCategory());

      await act(async () => {
        await result.current.handleDeleteCategory({...baseCategory, onDelete: jest.fn()});
      });

      expect(mockTranslate).toHaveBeenCalledWith(
        'pim_enrich.entity.category.category_deletion.error',
        {name: baseCategory.label}
      );
    });

    it('passes the category identifier to deleteCategory', async () => {
      const {result} = renderHook(() => useDeleteCategory());

      await act(async () => {
        await result.current.handleDeleteCategory({...baseCategory, identifier: 99});
      });

      expect(mockedDeleteCategory).toHaveBeenCalledWith(mockRouter, 99);
    });
  });
});
