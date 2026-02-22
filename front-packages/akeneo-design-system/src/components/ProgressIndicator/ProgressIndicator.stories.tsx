import type {Meta, StoryObj} from '@storybook/react';
import React from 'react';
import {ProgressIndicator} from './ProgressIndicator';
import {Button} from '../Button/Button';
import {useProgress} from '../../hooks';

const meta: Meta<typeof ProgressIndicator> = {
  title: 'Components/Progress indicator',
  component: ProgressIndicator,
  argTypes: {
    current: {
      name: '<ProgressIndicator current>',
      description: 'Define the current step of the progress',
    },
  },
};

export default meta;
type Story = StoryObj<typeof meta>;

export const States: Story = {
  name: 'States',
  render: () => (
    <ProgressIndicator>
      <ProgressIndicator.Step>Before current step</ProgressIndicator.Step>
      <ProgressIndicator.Step current={true}>Current step</ProgressIndicator.Step>
      <ProgressIndicator.Step>After current step</ProgressIndicator.Step>
    </ProgressIndicator>
  ),
};

export const Disabled: Story = {
  name: 'Disabled',
  render: () => (
    <ProgressIndicator>
      <ProgressIndicator.Step disabled={true}>Disabled step</ProgressIndicator.Step>
      <ProgressIndicator.Step disabled={false} current={true}>
        Current step enabled
      </ProgressIndicator.Step>
      <ProgressIndicator.Step disabled={true}>Next disabled step</ProgressIndicator.Step>
    </ProgressIndicator>
  ),
};

