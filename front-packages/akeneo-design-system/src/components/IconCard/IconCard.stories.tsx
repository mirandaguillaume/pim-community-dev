import React from 'react';
import {IconCardGrid as IconCardGridComponent, IconCard} from './IconCard';
import * as Icons from '../../icons';

export default {
  title: 'Components/IconCard',
  component: IconCard,

  argTypes: {
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

    onClick: {
      action: 'Click on the Card',
    },
  },

  args: {
    icon: 'ComponentIcon',
    label: 'Label',
    content: 'This is the content',
    disabled: false,
    onClick: () => console.log('click'),
  },
};

export const Standard = {
  render: args => {
    return (
      <IconCardGridComponent>
        <IconCard {...args} icon={React.createElement(Icons[args.icon])} />
      </IconCardGridComponent>
    );
  },

  name: 'Standard',
};

export const Readonly = {
  render: args => {
    return (
      <IconCardGridComponent>
        <IconCard {...args} icon={React.createElement(Icons[args.icon])} disabled={true} />
      </IconCardGridComponent>
    );
  },

  name: 'Readonly',
};

export const WithNoContent = {
  render: args => {
    return (
      <IconCardGridComponent>
        <IconCard icon={React.createElement(Icons[args.icon])} label={'Label'} />
      </IconCardGridComponent>
    );
  },

  name: 'With no content',
};

export const IconCardGrid = {
  render: args => {
    return (
      <IconCardGridComponent>
        <IconCard icon={React.createElement(Icons[args.icon])} label={'Label'} content={'Content'} />
        <IconCard
          icon={React.createElement(Icons[args.icon])}
          label={'Label Label Label Label Label Label Label Label Label'}
          content={
            'Content Content Content Content Content Content Content Content Content Content Content Content Content Content Content Content '
          }
        />
        <IconCard icon={React.createElement(Icons[args.icon])} label={'Label'} content={'Content'} />
        <IconCard icon={React.createElement(Icons[args.icon])} label={'Label'} content={'Content'} />
        <IconCard icon={React.createElement(Icons[args.icon])} label={'Label'} content={'Content'} />
      </IconCardGridComponent>
    );
  },

  name: 'IconCard grid',
};
