import type {Meta, StoryObj} from '@storybook/react';
import React from 'react';
import {Breadcrumb} from './Breadcrumb.tsx';

const meta: Meta<typeof Breadcrumb> = {
  title: 'Components/Breadcrumb',
  component: Breadcrumb,
  argTypes: {children: {table: {type: {summary: 'Breadcrumb.Step[]'}}}},
};

export default meta;
type Story = StoryObj<typeof meta>;

export const Standard: Story = {
  name: 'Standard',
  render: (args) => (
    <Breadcrumb {...args}>
          <Breadcrumb.Step href="#">first</Breadcrumb.Step>
          <Breadcrumb.Step href="#">second</Breadcrumb.Step>
          <Breadcrumb.Step>third</Breadcrumb.Step>
        </Breadcrumb>
  ),
};

export const Steps: Story = {
  name: 'Steps',
  render: (args) => (
    <>
          <Breadcrumb {...args}>
            <Breadcrumb.Step href="#variation-on-steps">first</Breadcrumb.Step>
            <Breadcrumb.Step>second</Breadcrumb.Step>
          </Breadcrumb>
          <Breadcrumb {...args}>
            <Breadcrumb.Step>Only one</Breadcrumb.Step>
          </Breadcrumb>
        </>
  ),
};

