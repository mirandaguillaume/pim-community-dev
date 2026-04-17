import styled from 'styled-components';

// Stryker disable all: styled-components CSS
//TODO replace with Skeleton RAC-445
const TablePlaceholder = styled.div`
  display: grid;
  grid-row-gap: 10px;

  > div {
    height: 54px;
  }
`;
// Stryker restore all

export {TablePlaceholder};
