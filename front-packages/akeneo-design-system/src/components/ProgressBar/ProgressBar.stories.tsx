import React from 'react';
import {ProgressBar} from './ProgressBar';

export default {
  title: 'Components/Progress bar',
  component: ProgressBar,

  argTypes: {
    percent: {
      control: {
        type: 'range',
        min: 0,
        max: 100,
        step: 1,
      },
    },
  },

  args: {
    level: 'primary',
    title: 'Lorem Ipsum is simply dummy text',
    progressLabel: '30 minutes left',
    percent: 50,
  },
};

export const Standard = {
  render: args => {
    return <ProgressBar {...args} />;
  },

  name: 'Standard',
};

export const Progress = {
  render: args => {
    return (
      <>
        <ProgressBar {...args} progressLabel="0%" percent={0} />
        <ProgressBar {...args} progressLabel="25%" percent={25} />
        <ProgressBar {...args} progressLabel="50%" percent={50} />
        <ProgressBar {...args} progressLabel="100%" percent={100} />
        <ProgressBar {...args} progressLabel="indeterminate" percent="indeterminate" />
      </>
    );
  },

  name: 'Progress',
};

export const Level = {
  render: args => {
    return (
      <>
        <ProgressBar {...args} title="Primary" level="primary" />
        <ProgressBar {...args} title="Secondary" level="secondary" />
        <ProgressBar {...args} title="Tertiary" level="tertiary" />
        <ProgressBar {...args} title="Warning" level="warning" />
        <ProgressBar {...args} title="Danger" level="danger" />
      </>
    );
  },

  name: 'Level',
};

export const Light = {
  render: args => {
    return (
      <>
        <ProgressBar {...args} title="Default" light={false} />
        <ProgressBar {...args} title="Light" light={true} />
      </>
    );
  },

  name: 'Light',
};

export const Size = {
  render: args => {
    return (
      <>
        <ProgressBar {...args} title="Small" size="small" />
        <ProgressBar {...args} title="Large" size="large" />
      </>
    );
  },

  name: 'Size',
};

export const Width = {
  render: args => {
    return (
      <>
        <div>
          <ProgressBar {...args} />
        </div>
        <div
          style={{
            width: '300px',
          }}
        >
          <ProgressBar {...args} />
        </div>
      </>
    );
  },

  name: 'Width',
};

export const Header = {
  render: args => {
    return (
      <>
        <div>
          <ProgressBar level="primary" percent={10} />
        </div>
        <br />
        <div>
          <ProgressBar level="primary" percent={10} title="Title without progress label" />
        </div>
        <div>
          <ProgressBar level="primary" percent={10} progressLabel="Progress label without title" />
        </div>
      </>
    );
  },

  name: 'Header',
};
