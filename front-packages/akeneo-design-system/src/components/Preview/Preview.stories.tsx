import type {Meta, StoryObj} from '@storybook/react';
import {action} from '@storybook/addon-actions';
import {useState} from 'react';
import {IconButton} from '../IconButton/IconButton';
import {SpaceContainer} from '../../storybook';
import {RefreshIcon} from '../../icons';
import {Preview} from './Preview.tsx';

const meta: Meta<typeof Preview> = {
  title: 'Components/Preview',
  component: Preview,
  args: {title: 'Preview', children: 'Preview text'},
};

export default meta;
type Story = StoryObj<typeof meta>;

export const Standard: Story = {
  name: 'Standard',
  render: (args) => (
    <Preview {...args} />
  ),
};

export const Highlight: Story = {
  name: 'Highlight',
  render: (args) => (
    <Preview {...args}>
          <Preview.Highlight>Name</Preview.Highlight> x <Preview.Highlight>Description</Preview.Highlight>
        </Preview>
  ),
};

export const Row: Story = {
  name: 'Row',
  render: (args) => (
    <SpaceContainer height={200}>
          <Preview {...args}>
            <Preview.Row>First row</Preview.Row>
            <Preview.Row>Second row</Preview.Row>
            <Preview.Row
              action={<IconButton icon={<RefreshIcon />} title="Reload third" onClick={action('Reload third row')} />}
            >
              Third row
            </Preview.Row>
            <Preview.Row
              action={<IconButton icon={<RefreshIcon />} title="Reload long" onClick={action('Reload long row')} />}
            >
              Logoden biniou degemer mat an penn ar bed werenn, baradoz hed c’haod moged gar galleg pakañ goañv merenn,
              hevelep Gwaien gegin gleb warnañ, Remengol morzhol.
            </Preview.Row>
            <Preview.Row>Fifth row</Preview.Row>
          </Preview>
        </SpaceContainer>
  ),
};

export const Collapsable: Story = {
  name: 'Collapsable',
  render: (args) => {
    <SpaceContainer height={200}>
          <Preview {...args} isOpen={isOpen} onCollapse={setOpen} collapseButtonLabel="Collapse">
            <Preview.Row>First row</Preview.Row>
            <Preview.Row>Second row</Preview.Row>
            <Preview.Row
              action={<IconButton icon={<RefreshIcon />} title="Reload third" onClick={action('Reload third row')} />}
            >
              Third row
            </Preview.Row>
            <Preview.Row>Fourth row</Preview.Row>
            <Preview.Row
              action={<IconButton icon={<RefreshIcon />} title="Reload long" onClick={action('Reload long row')} />}
            >
              Another one
            </Preview.Row>
          </Preview>
        </SpaceContainer>
  },
};

