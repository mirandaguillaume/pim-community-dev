import type {Meta, StoryObj} from '@storybook/react';
import React from 'react';
import {Pagination} from './Pagination.tsx';

const meta: Meta<typeof Pagination> = {
  title: 'Components/Pagination',
  component: Pagination,
  args: {
        totalItems: 3000,
        currentPage: 12,
        itemsPerPage: 25,
    },
};

export default meta;
type Story = StoryObj<typeof meta>;

export const Standard: Story = {
  name: 'Standard',
  render: (args) => (
    <Pagination {...args}/>
  ),
};

export const SmallNumberOfPages: Story = {
  name: 'Small number of pages',
  render: (args) => (
    return (
                <>
                    <Pagination totalItems={8} currentPage={1} itemsPerPage={2} onClick={() => {}}/>
                    <Pagination totalItems={3} currentPage={1} itemsPerPage={2} onClick={() => {}}/>
                </>
  ),
};

