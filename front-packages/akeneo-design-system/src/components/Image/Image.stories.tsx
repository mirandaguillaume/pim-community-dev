import React from 'react';
import {Image} from './Image';

export default {
  title: 'Components/Image',
  component: Image,

  args: {
    src: 'https://picsum.photos/seed/akeneo/200/140',
    alt: 'Image alt',
    width: 200,
    height: 140,
  },
};

export const Standard = {
  render: args => {
    return <Image {...args} />;
  },

  name: 'Standard',
};

export const Fit = {
  render: args => {
    return (
      <>
        <Image {...args} fit="contain" />
        <Image {...args} fit="cover" />
      </>
    );
  },

  name: 'Fit',
};

export const Stack = {
  render: args => {
    return (
      <>
        <Image {...args} />
        <Image {...args} isStacked />
      </>
    );
  },

  name: 'Stack',
};

export const Loading = {
  render: args => {
    return (
      <>
        <Image {...args} />
        <Image {...args} src={null} />
      </>
    );
  },

  name: 'Loading',
};
