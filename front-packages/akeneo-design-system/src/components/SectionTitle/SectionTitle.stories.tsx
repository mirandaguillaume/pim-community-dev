import React from 'react';
import {SectionTitle} from './SectionTitle';
import {Button, IconButton} from '../../components';
import {MoreIcon} from '../../icons';
import {Scrollable, Content} from '../../storybook/PreviewGallery';

export default {
  title: 'Components/Section title',
  component: SectionTitle,

  subcomponents: {
    'SectionTitle.Title': SectionTitle.Title,
  },

  args: {
    children: 'General parameters',
  },
};

export const Standard = {
  render: args => {
    return (
      <SectionTitle>
        <SectionTitle.Title>{args.children}</SectionTitle.Title>
      </SectionTitle>
    );
  },

  name: 'Standard',
};

export const Actions = {
  render: () => (
    <SectionTitle>
      <SectionTitle.Title>General parameters</SectionTitle.Title>
      <SectionTitle.Spacer />
      <SectionTitle.Information>10 results</SectionTitle.Information>
      <SectionTitle.Separator />
      <Button>Action</Button>
      <Button level="danger">Action</Button>
      <IconButton icon={<MoreIcon />} title="More" />
    </SectionTitle>
  ),

  name: 'Actions',
};

export const Sticky = {
  render: () => (
    <Scrollable height={300}>
      <SectionTitle sticky={0}>
        <SectionTitle.Title>First section</SectionTitle.Title>
        <SectionTitle.Spacer />
        <Button>Action</Button>
      </SectionTitle>
      <Content height={400}>Some content</Content>
      <SectionTitle sticky={0}>
        <SectionTitle.Title>Second section</SectionTitle.Title>
        <SectionTitle.Spacer />
        <Button>Action</Button>
      </SectionTitle>
      <Content height={400}>Some other content</Content>
    </Scrollable>
  ),

  name: 'Sticky',
};

export const TitleLevel = {
  render: () => (
    <SectionTitle>
      <SectionTitle.Title>Primary section</SectionTitle.Title>
      <SectionTitle.Spacer />
      <Button>Action</Button>
    </SectionTitle>
  ),

  name: 'Title level',
};
