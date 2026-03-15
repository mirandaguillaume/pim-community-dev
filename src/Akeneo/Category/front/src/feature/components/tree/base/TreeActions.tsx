import React, {FC, ReactNode} from 'react';
import styled from 'styled-components';

const Container = styled.div`
  display: flex;
  flex-direction: row;
  align-items: center;
  height: 100%;
  width: 100%;

  & > * {
    margin-left: 10px;
  }
`;

const TreeActions: FC<{children?: ReactNode}> = ({children}) => {
  return <Container>{children}</Container>;
};

export {TreeActions};
