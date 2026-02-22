import type {Meta, StoryObj} from '@storybook/react';
import React from 'react';
import {IconCardGrid, IconCard} from './IconCard';
import * as Icons from '../../icons';

const meta: Meta<typeof IconCard> = {
  title: 'Components/IconCard',
  component: IconCard,
  argTypes: {
        icon: {
            control: {type: 'select'}, options: Object.keys(Icons),
            table: {type: {summary: 'ReactElement<IconProps>'}},
        },
        onClick: {action: 'Click on the Card'},
    },
  args: {
        icon: 'ComponentIcon',
        label: 'Label',
        content: 'This is the content',
        disabled: false,
        onClick: () => console.log('click'),
    },
};

export default meta;
type Story = StoryObj<typeof meta>;

export const Standard: Story = {
  name: 'Standard',
  render: (args) => (
    <IconCardGrid>
                    <IconCard
                        {...args}
                        icon={React.createElement(Icons[args.icon])}
                    />
                </IconCardGrid>
  ),
};

export const Readonly: Story = {
  name: 'Readonly',
  render: (args) => (
    <IconCardGrid>
                    <IconCard
                        {...args}
                        icon={React.createElement(Icons[args.icon])}
                        disabled={true}
                    />
                </IconCardGrid>
  ),
};

export const WithNoContent: Story = {
  name: 'With no content',
  render: (args) => (
    <IconCardGrid>
                    <IconCard
                        icon={React.createElement(Icons[args.icon])}
                        label={'Label'}
                    />
                </IconCardGrid>
  ),
};

export const IconCardGrid: Story = {
  name: 'IconCard grid',
  render: (args) => (
    <IconCardGrid>
                    <IconCard
                        icon={React.createElement(Icons[args.icon])}
                        label={'Label'}
                        content={'Content'}
                    />
                    <IconCard
                        icon={React.createElement(Icons[args.icon])}
                        label={'Label Label Label Label Label Label Label Label Label'}
                        content={'Content Content Content Content Content Content Content Content Content Content Content Content Content Content Content Content '}
                    />
                    <IconCard
                        icon={React.createElement(Icons[args.icon])}
                        label={'Label'}
                        content={'Content'}
                    />
                    <IconCard
                        icon={React.createElement(Icons[args.icon])}
                        label={'Label'}
                        content={'Content'}
                    />
                    <IconCard
                        icon={React.createElement(Icons[args.icon])}
                        label={'Label'}
                        content={'Content'}
                    />
                </IconCardGrid>
  ),
};

