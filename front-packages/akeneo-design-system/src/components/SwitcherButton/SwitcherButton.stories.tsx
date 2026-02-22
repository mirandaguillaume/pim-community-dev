import type {Meta, StoryObj} from '@storybook/react';
import React from 'react';
import {SwitcherButton} from './SwitcherButton.tsx';
import {Locale} from '../Locale/Locale';
import styled from 'styled-components';
import {SpaceContainer} from '../../storybook/PreviewGallery';

const meta: Meta<typeof SwitcherButton> = {
  title: 'Components/Switcher Button',
  component: SwitcherButton,
  argTypes: {},
  args: {label: 'Label', children: 'Value'},
};

export default meta;
type Story = StoryObj<typeof meta>;

export const Standard: Story = {
  name: 'Standard',
  render: (args) => (
    <SpaceContainer width={200}>
          <SwitcherButton {...args}>{args.children}</SwitcherButton>
        </SpaceContainer>
  ),
};

export const Inline: Story = {
  name: 'Inline',
  render: (args) => (
    <>
          <SwitcherButton label="Inline">True</SwitcherButton>
          <SwitcherButton label="Inline" inline={false}>
            False
          </SwitcherButton>
        </>
  ),
};

export const Deletable: Story = {
  name: 'Deletable',
  render: (args) => (
    <>
          <SpaceContainer width={200}>
            <SwitcherButton label="Deletable" deletable inline={false}>
              Value
            </SwitcherButton>
          </SpaceContainer>
          <SpaceContainer width={200}>
            <SwitcherButton label="Not deletable" inline={false}>
              Value
            </SwitcherButton>
          </SpaceContainer>
        </>
  ),
};

export const Flag: Story = {
  name: 'Flag',
  render: (args) => (
    <>
          <SwitcherButton label="Locale">
            <Locale code="en_US" languageLabel="English" />
          </SwitcherButton>
          <SwitcherButton label="Locale" inline={false}>
            <Locale code="fr_FR" languageLabel="French" />
          </SwitcherButton>
        </>
  ),
};

