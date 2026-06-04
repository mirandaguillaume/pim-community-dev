import React, {useState} from 'react';
import {MessageBarContainer} from '../../storybook/PreviewGallery';
import LinkTo from '@storybook/addon-links/react';
import {MessageBar, AnimateMessageBar} from './MessageBar';
import {Link} from '../Link/Link';
import {Button} from '../Button/Button';
import * as Icons from '../../icons';

export default {
  title: 'Components/Message bar',
  component: MessageBar,

  argTypes: {
    level: {
      control: {
        type: 'select',
      },

      options: ['info', 'success', 'warning', 'error'],
    },

    icon: {
      control: {
        type: 'select',
      },

      options: Object.keys(Icons),

      table: {
        type: {
          summary: 'ReactElement<IconProps>',
        },
      },
    },

    children: {
      control: {
        type: 'text',
      },
    },

    onClose: {
      action: 'Closing the MessageBar',
    },
  },

  args: {
    level: 'info',
    title: "Don't be afraid to make these big decisions.",
    icon: 'ActivityIcon',
    children:
      "Once you start, they sort of just make themselves. This is probably the greatest thing that's ever happened in my life.",
  },
};

export const Standard = {
  render: args => {
    return <MessageBar {...args} icon={React.createElement(Icons[args.icon])} />;
  },

  name: 'Standard',
};

export const Level = {
  render: args => {
    return (
      <>
        <MessageBar {...args} title="Info" level="info" icon={<Icons.InfoRoundIcon />} />
        <MessageBar {...args} title="Success" level="success" icon={<Icons.GiftIcon />} />
        <MessageBar {...args} title="Warning" level="warning" icon={<Icons.HelpIcon />} />
        <MessageBar {...args} title="Error" level="error" icon={<Icons.DangerIcon />} />
      </>
    );
  },

  name: 'Level',
};

export const Content = {
  render: args => {
    delete args.children;
    delete args.icon;

    return (
      <>
        <MessageBar {...args} level="success" title="This one has a title only" />
        <MessageBar {...args} title="This one has a Link" icon={<Icons.LinkIcon />}>
          This one also has a Link. <Link>Take a look here</Link>
        </MessageBar>
      </>
    );
  },

  name: 'Content',
};

export const Animate = {
  render: args => {
    const [key, setKey] = useState(false);
    const toggle = () => setKey(!key);

    return (
      <>
        <MessageBarContainer>
          <AnimateMessageBar key={key}>
            <MessageBar {...args} title="Info" level="info" icon={<Icons.InfoRoundIcon />} />
          </AnimateMessageBar>
        </MessageBarContainer>
        <Button onClick={toggle}>Replay</Button>
      </>
    );
  },

  name: 'Animate',
};
