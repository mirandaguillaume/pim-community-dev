import type {Meta, StoryObj} from '@storybook/react';
import React from 'react';
import {ProgressBar} from './ProgressBar';

const meta: Meta<typeof ProgressBar> = {
  title: 'Components/Progress bar',
  component: ProgressBar,
  argTypes: {
    percent: {control: {type: 'range', min: 0, max: 100, step: 1}},
  },
  args: {
    level: 'primary',
    title: 'Lorem Ipsum is simply dummy text',
    progressLabel: '30 minutes left',
    percent: 50
  },
};

export default meta;
type Story = StoryObj<typeof meta>;

export const Standard: Story = {
  name: 'Standard',
  render: (args) => (
    <ProgressBar {...args} />
  ),
};

export const Progress: Story = {
  name: 'Progress',
  render: (args) => (
    <>
          <ProgressBar {...args} progressLabel="0%" percent={0} />
          <ProgressBar {...args} progressLabel="25%" percent={25} />
          <ProgressBar {...args} progressLabel="50%" percent={50} />
          <ProgressBar {...args} progressLabel="100%" percent={100} />
          <ProgressBar {...args} progressLabel="indeterminate" percent='indeterminate' />
        </>
  ),
};

export const Level: Story = {
  name: 'Level',
  render: (args) => (
    <>
          <ProgressBar {...args} title="Primary" level="primary" />
          <ProgressBar {...args} title="Secondary" level="secondary" />
          <ProgressBar {...args} title="Tertiary" level="tertiary" />
          <ProgressBar {...args} title="Warning" level="warning" />
          <ProgressBar {...args} title="Danger" level="danger" />
        </>
  ),
};

export const Light: Story = {
  name: 'Light',
  render: (args) => (
    <>
          <ProgressBar {...args} title="Default" light={false} />
          <ProgressBar {...args} title="Light" light={true} />
        </>
  ),
};

export const Size: Story = {
  name: 'Size',
  render: (args) => (
    <>
          <ProgressBar {...args} title="Small" size="small" />
          <ProgressBar {...args} title="Large" size="large" />
        </>
  ),
};

export const Width: Story = {
  name: 'Width',
  render: (args) => (
    <>
          <div>
            <ProgressBar {...args}/>
          </div>
          <div style={{width: '300px'}}>
            <ProgressBar {...args}/>
          </div>
        </>
  ),
};

export const Header: Story = {
  name: 'Header',
  render: (args) => (
    <>
          <div>
            <ProgressBar level="primary" percent={10} />
          </div>
          <br/>
          <div>
            <ProgressBar level="primary" percent={10} title="Title without progress label"/>
          </div>
          <div>
            <ProgressBar level="primary" percent={10} progressLabel="Progress label without title"/>
          </div>
        </>
  ),
};

