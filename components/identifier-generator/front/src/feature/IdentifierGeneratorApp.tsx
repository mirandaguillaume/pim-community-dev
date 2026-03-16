import React, {useMemo} from 'react';
import {createHashRouter, createRoutesFromElements, Route, RouterProvider} from 'react-router-dom';
import {Edit, List} from './controllers';
import {QueryClient, QueryClientProvider} from 'react-query';
import styled from 'styled-components';
import {getColor} from 'akeneo-design-system';
import {IdentifierGeneratorAclContextProvider} from './context/IdentifierGeneratorAclContextProvider';
import {FullScreenError, useSecurity, useTranslate} from '@akeneo-pim-community/shared';

const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      refetchOnWindowFocus: false,
      retry: false,
      staleTime: Infinity,
    },
  },
});

const ContainerApp = styled.div`
  color: ${getColor('grey', 120)};
`;

const IdentifierGeneratorApp: React.FC = () => {
  const {isGranted} = useSecurity();
  const translate = useTranslate();
  const hasViewPermission = useMemo(
    () => isGranted('pim_identifier_generator_view') || isGranted('pim_identifier_generator_manage'),
    [isGranted]
  );

  const router = useMemo(
    () =>
      createHashRouter(
        createRoutesFromElements(
          <>
            <Route path="/:identifierGeneratorCode" element={<Edit />} />
            <Route path="/" element={<List />} />
          </>
        ),
        {basename: '/configuration/identifier-generator'}
      ),
    []
  );

  return hasViewPermission ? (
    <ContainerApp>
      <QueryClientProvider client={queryClient}>
        <IdentifierGeneratorAclContextProvider>
          <RouterProvider router={router} />
        </IdentifierGeneratorAclContextProvider>
      </QueryClientProvider>
    </ContainerApp>
  ) : (
    <FullScreenError
      title={translate('error.exception', {status_code: 403})}
      message={translate('error.forbidden')}
      code={403}
    />
  );
};

export {IdentifierGeneratorApp};
