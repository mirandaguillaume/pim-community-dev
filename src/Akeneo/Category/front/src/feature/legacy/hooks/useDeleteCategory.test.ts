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

describe('useDeleteCategory (legacy)', () => {
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
    it('returns true when numberOfProducts is exactly 100', () => {
      const {result} = renderHook(() => useDeleteCategory());
      expect(result.current.isCategoryDeletionPossible('Master', 100)).toBe(true);
    });

    it('returns false and notifies when numberOfProducts exceeds 100', () => {
      const {result} = renderHook(() => useDeleteCategory());
      expect(result.current.isCategoryDeletionPossible('Master', 101)).toBe(false);
      expect(mockNotify).toHaveBeenCalledWith(
        NotificationLevel.INFO,
        'pim_enrich.entity.category.category_deletion.products_limit_exceeded.title',
        'pim_enrich.entity.category.category_deletion.products_limit_exceeded.message'
      );
    });
  });

  describe('handleDeleteCategory', () => {
    it('calls onDelete and notifies success when response is ok', async () => {
      mockedDeleteCategory.mockResolvedValue({ok: true, errorMessage: ''});
      const onDelete = jest.fn();
      const {result} = renderHook(() => useDeleteCategory());

      await act(async () => {
        await result.current.handleDeleteCategory({identifier: 1, label: 'Root', onDelete});
      });

      expect(onDelete).toHaveBeenCalled();
      expect(mockNotify).toHaveBeenCalledWith(
        NotificationLevel.SUCCESS,
        'pim_enrich.entity.category.category_deletion.success'
      );
    });

    it('does not call onDelete and notifies with errorMessage when response fails', async () => {
      mockedDeleteCategory.mockResolvedValue({ok: false, errorMessage: 'Not allowed'});
      const onDelete = jest.fn();
      const {result} = renderHook(() => useDeleteCategory());

      await act(async () => {
        await result.current.handleDeleteCategory({identifier: 1, label: 'Root', onDelete});
      });

      expect(onDelete).not.toHaveBeenCalled();
      expect(mockNotify).toHaveBeenCalledWith(NotificationLevel.ERROR, 'Not allowed');
    });

    it('falls back to generic error key when errorMessage is empty', async () => {
      mockedDeleteCategory.mockResolvedValue({ok: false, errorMessage: ''});
      const {result} = renderHook(() => useDeleteCategory());

      await act(async () => {
        await result.current.handleDeleteCategory({identifier: 1, label: 'Root', onDelete: jest.fn()});
      });

      expect(mockTranslate).toHaveBeenCalledWith(
        'pim_enrich.entity.category.category_deletion.error',
        {name: 'Root'}
      );
    });
  });
});
