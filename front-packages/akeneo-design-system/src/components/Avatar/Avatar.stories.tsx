import type {Meta, StoryObj} from '@storybook/react';
import React from 'react';
import {Avatar} from './Avatar.tsx';
import {Avatars} from './Avatars.tsx';

const meta: Meta<typeof Avatar> = {
  title: 'Components/Avatar',
  component: Avatar,
  argTypes: {
    firstName: {control: {type: 'text'}},
    lastName: {control: {type: 'text'}},
    username: {control: {type: 'text'}},
    avatarUrl: {control: {type: 'text'}},
    size: {control: {type: 'select', options: ['default', 'big']}},
  },
  args: {
    firstName: 'John',
    lastName: 'Doe',
    username: 'admin',
    avatarUrl: undefined,
    size: 'default',
  },
};

export default meta;
type Story = StoryObj<typeof meta>;

export const Standard: Story = {
  name: 'Standard',
  render: (args) => (
    <Avatar firstName={'John'} lastName={'Doe'} username={'admin'} {...args} />
  ),
};

export const BackgroundColors: Story = {
  name: 'Background colors',
  render: (args) => (
    <>
          <Avatar {...args} firstName={'Albert'} lastName={'Doe'} username={'a'} />
          <Avatar {...args} firstName={'Bertrand'} lastName={'Doe'} username={'b'} />
          <Avatar {...args} firstName={'Chris'} lastName={'Doe'} username={'c'} />
          <Avatar {...args} firstName={'Danny'} lastName={'Doe'} username={'d'} />
          <Avatar {...args} firstName={'Elon'} lastName={'Doe'} username={'e'} />
          <Avatar {...args} firstName={'Fred'} lastName={'Doe'} username={'f'} />
          <Avatar {...args} firstName={'Gus'} lastName={'Doe'} username={'g'} />
          <Avatar {...args} firstName={'Helen'} lastName={'Doe'} username={'h'} />
          <Avatar {...args} firstName={'Isabel'} lastName={'Doe'} username={'i'} />
          <Avatar {...args} firstName={'John'} lastName={'Doe'} username={'j'} />
          <Avatar {...args} firstName={'Kurt'} lastName={'Doe'} username={'k'} />
          <Avatar {...args} firstName={'Leonard'} lastName={'Doe'} username={'l'} />
        </>
  ),
};

export const WithImage: Story = {
  name: 'With image',
  render: (args) => (
    <>
          <Avatar {...args} avatarUrl={'https://picsum.photos/seed/akeneo/32/32'} />
        </>
  ),
};

export const AvatarList: Story = {
  name: 'Avatar list',
  render: (args) => (
    <>
          <Avatars
            max={5}
            maxTitle={5}
          >
            <Avatar
              {...args}
              firstName={'Albert'}
              lastName={'Doe'}
              username={'a'}
              avatarUrl={'https://picsum.photos/seed/akeneo/32/32'}
            />
            <Avatar {...args} firstName={'Bertrand'} lastName={'Doe'} username={'b'} />
            <Avatar
              {...args}
              firstName={'Chris'}
              lastName={'Doe'}
              username={'c'}
              avatarUrl={'https://picsum.photos/seed/bkeneo/32/32'}
            />
            <Avatar {...args} firstName={'Danny'} lastName={'Doe'} username={'d'} />
            <Avatar
              {...args}
              firstName={'Elon'}
              lastName={'Doe'}
              username={'e'}
              avatarUrl={'https://picsum.photos/seed/ckeneo/32/32'}
            />
            <Avatar {...args} firstName={'Fred'} lastName={'Doe'} username={'f'} />
            <Avatar {...args} firstName={'Gus'} lastName={'Doe'} username={'g'} />
            <Avatar {...args} firstName={'Helen'} lastName={'Doe'} username={'h'} />
            <Avatar {...args} firstName={'Isabel'} lastName={'Doe'} username={'i'} />
            <Avatar {...args} firstName={'John'} lastName={'Doe'} username={'j'} />
            <Avatar {...args} firstName={'Kurt'} lastName={'Doe'} username={'k'} />
            <Avatar {...args} firstName={'Leonard'} lastName={'Doe'} username={'l'} />
          </Avatars>
        </>
  ),
};

export const AvatarSize: Story = {
  name: 'Avatar size',
  render: (args) => (
    <>
          <Avatar {...args} size={'default'} />
          <Avatar {...args} size={'big'} />
        </>
  ),
};

