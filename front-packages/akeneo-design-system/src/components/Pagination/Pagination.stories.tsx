import React from 'react';
import {Pagination} from './Pagination';

export default {
  title: 'Components/Pagination',
  component: Pagination,

  args: {
    totalItems: 3000,
    currentPage: 12,
    itemsPerPage: 25,
  },
};

export const Standard = {
  render: args => {
    return <Pagination {...args} />;
  },

  name: 'Standard',
};

export const SmallNumberOfPages = {
  render: args => {
    return (
      <>
        <Pagination totalItems={8} currentPage={1} itemsPerPage={2} onClick={() => {}} />
        <Pagination totalItems={3} currentPage={1} itemsPerPage={2} onClick={() => {}} />
      </>
    );
  },

  name: 'Small number of pages',
};
