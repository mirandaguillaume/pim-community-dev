import React from 'react';
import {SwitcherButton} from './SwitcherButton';
import {Locale} from '../Locale/Locale';
import styled from 'styled-components';
import {SpaceContainer} from '../../storybook/PreviewGallery';

export default {
  title: 'Components/Switcher Button',
  component: SwitcherButton,
  argTypes: {},

  args: {
    label: 'Label',
    children: 'Value',
  },
};

export const Standard = {
  render: args => {
    return (
      <SpaceContainer width={200}>
        <SwitcherButton {...args}>{args.children}</SwitcherButton>
      </SpaceContainer>
    );
  },

  name: 'Standard',
};

export const Inline = {
  render: args => {
    return (
      <>
        <SwitcherButton label="Inline">True</SwitcherButton>
        <SwitcherButton label="Inline" inline={false}>
          False
        </SwitcherButton>
      </>
    );
  },

  name: 'Inline',
};

export const Deletable = {
  render: args => {
    return (
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
    );
  },

  name: 'Deletable',
};

export const Flag = {
  render: args => {
    return (
      <>
        <SwitcherButton label="Locale">
          <Locale code="en_US" languageLabel="English" />
        </SwitcherButton>
        <SwitcherButton label="Locale" inline={false}>
          <Locale code="fr_FR" languageLabel="French" />
        </SwitcherButton>
      </>
    );
  },

  name: 'Flag',
};
