import React, {useState} from 'react';
import {action} from '@storybook/addon-actions';
import {IconButton} from '../IconButton/IconButton';
import {SpaceContainer} from '../../storybook';
import {RefreshIcon} from '../../icons';
import {Preview} from './Preview';

export default {
  title: 'Components/Preview',
  component: Preview,

  subcomponents: {
    'Preview.Highlight': Preview.Highlight,
    'Preview.Row': Preview.Row,
  },

  args: {
    title: 'Preview',
    children: 'Preview text',
  },
};

export const Standard = {
  render: args => {
    return <Preview {...args} />;
  },

  name: 'Standard',
};

export const Highlight = {
  render: args => {
    return (
      <Preview {...args}>
        <Preview.Highlight>Name</Preview.Highlight>x <Preview.Highlight>Description</Preview.Highlight>
      </Preview>
    );
  },

  name: 'Highlight',
};

export const Row = {
  render: args => {
    return (
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
    );
  },

  name: 'Row',
};

export const Collapsable = {
  render: args => {
    const [isOpen, setOpen] = useState(false);

    return (
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
    );
  },

  name: 'Collapsable',
};
