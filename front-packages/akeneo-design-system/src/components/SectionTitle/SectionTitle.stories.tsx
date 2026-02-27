import type {Meta, StoryObj} from '@storybook/react';
import React from 'react';
import {SectionTitle} from './SectionTitle.tsx';
import {Button, IconButton} from '../../components';
import {MoreIcon} from '../../icons';
import {Scrollable, Content} from '../../storybook/PreviewGallery';

const meta: Meta<typeof SectionTitle> = {
  title: 'Components/Section title',
  component: SectionTitle,
  args: {
    children: 'General parameters',
  },
};

export default meta;
type Story = StoryObj<typeof meta>;

export const Standard: Story = {
  name: 'Standard',
  render: (args) => (
    <SectionTitle>
          <SectionTitle.Title>{args.children}</SectionTitle.Title>
        </SectionTitle>
  ),
};

export const Actions: Story = {
  name: 'Actions',
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
};

export const Sticky: Story = {
  name: 'Sticky',
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
};

export const TitleLevel: Story = {
  name: 'Title level',
  render: () => (
    <SectionTitle>
      <SectionTitle.Title>Primary section</SectionTitle.Title>
      <SectionTitle.Spacer />
      <Button>Action</Button>
    </SectionTitle>
    <SectionTitle>
      <SectionTitle.Title level="secondary">Secondary section</SectionTitle.Title>
      <SectionTitle.Spacer />
      <Button>Action</Button>
    </SectionTitle>
  ),
};

