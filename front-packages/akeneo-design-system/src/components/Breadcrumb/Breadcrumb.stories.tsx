import React from 'react';
import {Breadcrumb} from './Breadcrumb';

export default {
  title: 'Components/Breadcrumb',
  component: Breadcrumb,

  argTypes: {
    children: {
      table: {
        type: {
          summary: 'Breadcrumb.Step[]',
        },
      },
    },
  },
};

export const Standard = {
  render: args => {
    return (
      <Breadcrumb {...args}>
        <Breadcrumb.Step href="#">first</Breadcrumb.Step>
        <Breadcrumb.Step href="#">second</Breadcrumb.Step>
        <Breadcrumb.Step>third</Breadcrumb.Step>
      </Breadcrumb>
    );
  },

  name: 'Standard',
};

export const Steps = {
  render: args => {
    return (
      <>
        <Breadcrumb {...args}>
          <Breadcrumb.Step href="#variation-on-steps">first</Breadcrumb.Step>
          <Breadcrumb.Step>second</Breadcrumb.Step>
        </Breadcrumb>
        <Breadcrumb {...args}>
          <Breadcrumb.Step>Only one</Breadcrumb.Step>
        </Breadcrumb>
      </>
    );
  },

  name: 'Steps',
};
