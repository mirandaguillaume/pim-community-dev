import React, {useState, createElement} from 'react';
import styled from 'styled-components';
import {action} from '@storybook/addon-actions';
import {Placeholder} from './Placeholder';
import {UsersIllustration} from '../../illustrations';
import * as Illustrations from '../../illustrations';

export default {
  title: 'Components/Placeholder',
  component: Placeholder,

  argTypes: {
    illustration: {
      control: {
        type: 'select',
      },

      options: Object.keys(Illustrations),
    },

    title: {
      control: {
        type: 'text',
      },
    },

    children: {
      control: {
        type: 'text',
      },
    },
  },

  args: {
    illustration: 'UsersIllustration',
    title: 'Placeholder title',
    children: 'Placeholder children text',
  },
};

export const Standard = {
  render: args => {
    const IllustrationComponent = createElement(Illustrations[args.illustration]);
    return <Placeholder {...args} illustration={IllustrationComponent} />;
  },

  name: 'Standard',
};

export const Size = {
  render: args => {
    const IllustrationComponent = createElement(Illustrations.AttributesIllustration);

    return <Placeholder {...args} title="Large placeholder" illustration={IllustrationComponent} size="large" />;
  },

  name: 'Size',
};
