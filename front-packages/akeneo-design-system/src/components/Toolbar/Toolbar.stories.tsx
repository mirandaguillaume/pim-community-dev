import React from 'react';
import {Toolbar} from './Toolbar';
import {Button, Checkbox} from '../../components';
import {SpaceContainer, useSelection} from '../../storybook';

export default {
  title: 'Components/Toolbar',
  component: Toolbar,
};

export const Standard = {
  render: args => {
    const selection = useSelection();

    return (
      <SpaceContainer height={250}>
        <SpaceContainer
          style={{
            flexGrow: 1,
          }}
        />
        <Toolbar {...args}>
          <Toolbar.SelectionContainer>
            <Checkbox {...selection} />
          </Toolbar.SelectionContainer>
          <Toolbar.LabelContainer>{selection.checked ? 'All selected' : 'Not selected'}</Toolbar.LabelContainer>
          <Toolbar.ActionsContainer>
            <Button level="secondary">Launch</Button>
            <Button level="tertiary">Another one</Button>
            <Button level="danger">Cancel</Button>
          </Toolbar.ActionsContainer>
        </Toolbar>
      </SpaceContainer>
    );
  },

  name: 'Standard',
};
