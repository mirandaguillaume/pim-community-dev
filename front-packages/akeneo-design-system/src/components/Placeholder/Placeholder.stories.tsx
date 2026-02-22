import type {Meta, StoryObj} from '@storybook/react';
import React from 'react';
import {useState, createElement} from 'react';
import styled from 'styled-components';
import {Placeholder} from './Placeholder.tsx';
import {UsersIllustration} from '../../illustrations';
import * as Illustrations from '../../illustrations';

const meta: Meta<typeof Placeholder> = {
  title: 'Components/Placeholder',
  component: Placeholder,
  argTypes: {
    illustration: {control: {type: 'select'}, options: Object.keys(Illustrations)},
    title: {control: {type: 'text'}},
    children: {control: {type: 'text'}},
  },
  args: {
    illustration: 'UsersIllustration',
    title: 'Placeholder title',
    children: 'Placeholder children text',
  },
};

export default meta;
type Story = StoryObj<typeof meta>;

export const Standard: Story = {
  name: 'Standard',
  render: (args) => (
    const IllustrationComponent = createElement(Illustrations[args.illustration]);
      return <Placeholder {...args} illustration={IllustrationComponent} />;
  ),
};

export const Size: Story = {
  name: 'Size',
  render: (args) => (
    const IllustrationComponent = createElement(Illustrations.AttributesIllustration);
      return <Placeholder {...args} title="Large placeholder" illustration={IllustrationComponent} size="large" />;
  ),
};

