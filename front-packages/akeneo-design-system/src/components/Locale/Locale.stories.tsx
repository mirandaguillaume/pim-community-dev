import type {Meta, StoryObj} from '@storybook/react';
import React from 'react';
import {Locale} from './Locale.tsx';

const meta: Meta<typeof Locale> = {
  title: 'Components/Locale',
  component: Locale,
  args: {code: 'en_US'},
};

export default meta;
type Story = StoryObj<typeof meta>;

export const Standard: Story = {
  name: 'Standard',
  render: (args) => (
    <Locale {...args} />
  ),
};

export const Label: Story = {
  name: 'Label',
  render: (args) => (
    <>
          <Locale code="en_US" languageLabel="English" />
          <Locale code="fr_FR" languageLabel="Français" />
          <Locale code="de_DE" />
          <Locale code="pt_BR" />
        </>
  ),
};

