import {FC, StrictMode, useMemo} from 'react';
import {QueryClient, QueryClientProvider} from 'react-query';
import {createHashRouter, createRoutesFromElements, Route, RouterProvider} from 'react-router-dom';
import {ErrorBoundary} from './ErrorBoundary';
import {CanLeavePageProvider, EditCategoryProvider} from './components';
import {SaveStatusProvider} from './components/providers/SaveStatusProvider';
import {TemplateFormProvider} from './components/providers/TemplateFormProvider';
import {UnsavedChangesGuard} from './components/templates/UnsavedChangeGuard';
import {CategoriesIndex, CategoriesTreePage, CategoryEditPage, TemplatePage} from './pages';
import {BadRequestError} from './tools/apiFetch';

const useErrorBoundary = (error: unknown) => false === error instanceof BadRequestError;

const router = createHashRouter(
  createRoutesFromElements(
    <>
      <Route path="/:treeId/tree" element={<CategoriesTreePage />} />
      <Route
        path="/:categoryId/edit"
        element={
          <EditCategoryProvider>
            <CategoryEditPage />
          </EditCategoryProvider>
        }
      />
      <Route
        path="/:treeId/template/:templateId"
        element={
          <SaveStatusProvider>
            <UnsavedChangesGuard />
            <TemplateFormProvider>
              <TemplatePage />
            </TemplateFormProvider>
          </SaveStatusProvider>
        }
      />
      <Route path="/" element={<CategoriesIndex />} />
    </>
  ),
  {basename: '/enrich/product-category-tree'}
);

type Props = {
  setCanLeavePage: (canLeavePage: boolean) => void;
  setLeavePageMessage: (leavePageMessage: string) => void;
};

const CategoriesApp: FC<Props> = ({setCanLeavePage, setLeavePageMessage}) => {
  const queryClient = useMemo(
    () =>
      new QueryClient({
        defaultOptions: {
          queries: {useErrorBoundary},
          mutations: {useErrorBoundary},
        },
      }),
    []
  );

  return (
    <StrictMode>
      <ErrorBoundary>
        <QueryClientProvider client={queryClient}>
          <CanLeavePageProvider setCanLeavePage={setCanLeavePage} setLeavePageMessage={setLeavePageMessage}>
            <RouterProvider router={router} />
          </CanLeavePageProvider>
        </QueryClientProvider>
      </ErrorBoundary>
    </StrictMode>
  );
};

export {CategoriesApp};
