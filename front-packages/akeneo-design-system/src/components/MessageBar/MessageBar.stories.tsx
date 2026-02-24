import type {Meta, StoryObj} from '@storybook/react';
import {useState} from 'react';
import {MessageBarContainer} from '../../storybook/PreviewGallery';
import {MessageBar, AnimateMessageBar} from './MessageBar.tsx';
import {Link} from '../Link/Link';
import {Button} from '../Button/Button';
import * as Icons from '../../icons';

const meta: Meta<typeof MessageBar> = {
  title: 'Components/Message bar',
  component: MessageBar,
  argTypes: {
    level: {control: {type: 'select'}, options: ['info', 'success', 'warning', 'error']},
    icon: {
      control: {type: 'select'}, options: Object.keys(Icons),
      table: {type: {summary: 'ReactElement<IconProps>'}},
    },
    children: {control: {type: 'text'}},
    onClose: {action: 'Closing the MessageBar'},
  },
  args: {
    level: 'info',
    title: "Don't be afraid to make these big decisions.",
    icon: 'ActivityIcon',
    children:
      "Once you start, they sort of just make themselves. This is probably the greatest thing that's ever happened in my life.",
  },
};

export default meta;
type Story = StoryObj<typeof meta>;

export const Standard: Story = {
  name: 'Standard',
  render: (args) => (
    <MessageBar {...args} icon={React.createElement(Icons[args.icon])} />
  ),
};

export const Level: Story = {
  name: 'Level',
  render: (args) => (
    <>
          <MessageBar {...args} title="Info" level="info" icon={<Icons.InfoRoundIcon />} />
          <MessageBar {...args} title="Success" level="success" icon={<Icons.GiftIcon />} />
          <MessageBar {...args} title="Warning" level="warning" icon={<Icons.HelpIcon />} />
          <MessageBar {...args} title="Error" level="error" icon={<Icons.DangerIcon />} />
        </>
  ),
};

export const Content: Story = {
  name: 'Content',
  render: (args) => (
    <>
          <MessageBar {...args} level="success" title="This one has a title only" />
          <MessageBar {...args} title="This one has a Link" icon={<Icons.LinkIcon />}>
            This one also has a Link. <Link>Take a look here</Link>
          </MessageBar>
        </>
  ),
};

export const Animate: Story = {
  name: 'Animate',
  render: (args) => {
    <>
          <MessageBarContainer>
            <AnimateMessageBar key={key}>
              <MessageBar {...args} title="Info" level="info" icon={<Icons.InfoRoundIcon />} />
            </AnimateMessageBar>
          </MessageBarContainer>
          <Button onClick={toggle}>Replay</Button>
        </>
  },
};

