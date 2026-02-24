import type {Meta, StoryObj} from '@storybook/react';
import React from 'react';
import {MyComponent} from './MyComponent.tsx';

const meta: Meta<typeof MyComponent> = {
  title: 'Components/MyComponent',
  component: MyComponent,
  argTypes: {
    level: {control: {type: 'select', options: ['primary', 'warning', 'danger']}},
  },
  args: {level: 'primary', children: 'MyComponent text'},
};

export default meta;
type Story = StoryObj<typeof meta>;

export const Standard: Story = {
  name: 'Standard',
  render: (args) => (
    <MyComponent {...args} />
  ),
};

export const Level: Story = {
  name: 'Level',
  render: (args) => (
    <>
          <MyComponent {...args} level="primary">
            Primary
          </MyComponent>
          <MyComponent {...args} level="warning">
            Warning
          </MyComponent>
          <MyComponent {...args} level="danger">
            Danger
          </MyComponent>
        </>
  ),
};

