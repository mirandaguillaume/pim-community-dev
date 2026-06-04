import React from 'react';
import {MyComponent} from './MyComponent';

export default {
  title: 'Components/MyComponent',
  component: MyComponent,

  argTypes: {
    level: {
      control: {
        type: 'select',
        options: ['primary', 'warning', 'danger'],
      },
    },
  },

  args: {
    level: 'primary',
    children: 'MyComponent text',
  },
};

export const Standard = {
  render: args => {
    return <MyComponent {...args} />;
  },

  name: 'Standard',
};

export const Level = {
  render: args => {
    return (
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
    );
  },

  name: 'Level',
};
