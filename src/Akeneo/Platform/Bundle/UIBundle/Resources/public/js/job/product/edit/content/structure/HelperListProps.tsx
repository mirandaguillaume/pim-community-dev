import React from 'react';
import {Helper} from 'akeneo-design-system';
import styled from 'styled-components';

type HelperListProps = {
  elements: {
    text: string;
    link: {
      text: string;
      href: string;
    };
  }[];
};

const Container = styled.div`
  display: flex;
  flex-direction: column;
  gap: 5px;
  margin: 0 20px 20px;
`;

const HelperList: React.FC<HelperListProps> = ({elements}) => {
  return (
    <Container>
      {elements.map((item, index) => {
        return (
          <Helper key={index}>
            {item.text} <a href={item.link.href}>{item.link.text}</a>
          </Helper>
        );
      })}
    </Container>
  );
};

export {HelperList};
