import type {Meta, StoryObj} from '@storybook/react';
import React from 'react';
import {Helper} from './Helper';
import {Link} from '../Link/Link';
import * as Icons from '../../icons';
import {Scrollable, Content} from '../../storybook/PreviewGallery';

const meta: Meta<typeof Helper> = {
  title: 'Components/Helper',
  component: Helper,
  argTypes: {
    children: {control: {type: 'text'}},
    icon: {
      control: {type: 'select'}, options: [undefined, ...Object.keys(Icons)],
      table: {type: {summary: 'ReactElement<IconProps>'}},
    },
  },
  args: {
    children:
      'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
    level: 'info',
    icon: undefined,
  },
};

export default meta;
type Story = StoryObj<typeof meta>;

export const Standard: Story = {
  name: 'Standard',
  render: (args) => (
    <Helper {...args} icon={undefined === Icons[args.icon] ? undefined : React.createElement(Icons[args.icon])} />
  ),
};

export const Composition: Story = {
  name: 'Composition',
  render: (args) => (
    <Helper level="info">
          {`Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed non risus.Suspendisse lectus tortor,
dignissim sit amet, adipiscing nec, ultricies sed, dolor. Cras elementum ultrices diam.
Maecenas ligula massa, varius a, semper congue, euismod non, mi. Proin porttitor, orci nec nonummy molestie, enim est eleifend mi,
non fermentum diam nisl sit amet erat. `}
          <Link target="_blank" href="https://www.akeneo.com/">
            Link to related lorem ipsum
          </Link>
        </Helper>
  ),
};

export const Level: Story = {
  name: 'Level',
  render: (args) => (
    <>
          <Helper {...args} level="info">
            You might need to read this. <Link href="#">Link</Link>
          </Helper>
          <Helper {...args} level="warning">
            There is a warning. <Link href="#">Link</Link>
          </Helper>
          <Helper {...args} level="error">
            There is an error. <Link href="#">Link</Link>
          </Helper>
          <Helper {...args} level="success">
            There is a success. <Link href="#">Link</Link>
          </Helper>
        </>
  ),
};

export const Inline: Story = {
  name: 'Inline',
  render: (args) => (
    <>
          <Helper {...args} inline={true} level="info">
            You might need to read this. <Link href="#">Link</Link>
          </Helper>
          <Helper {...args} inline={true} level="warning">
            There is a warning. <Link href="#">Link</Link>
          </Helper>
          <Helper {...args} inline={true} level="error">
            There is an error. <Link href="#">Link</Link>
          </Helper>
          <Helper {...args} inline={true} level="success">
            There is a success. <Link href="#">Link</Link>
          </Helper>
        </>
  ),
};

export const Sticky: Story = {
  name: 'Sticky',
  render: (args) => (
    <Scrollable height={300}>
          <Helper level="warning" sticky={0}>
            This is a sticky helper.
          </Helper>
          <Content height={500}>Some content</Content>
        </Scrollable>
  ),
};

export const CustomIcon: Story = {
  name: 'Custom Icon',
  render: (args) => (
    <>
          <Helper {...args} level="info" icon={<Icons.BrokenLinkIcon />}>
            Info
          </Helper>
          <Helper {...args} level="warning" icon={<Icons.MegaphoneIcon />}>
            Warning
          </Helper>
          <Helper {...args} level="error" inline={true} icon={<Icons.UnviewIcon />}>
            Error level and inline
          </Helper>
          <Helper {...args} level="success" inline={true} icon={<Icons.MailIcon />}>
            Success level and inline
          </Helper>
        </>
  ),
};

