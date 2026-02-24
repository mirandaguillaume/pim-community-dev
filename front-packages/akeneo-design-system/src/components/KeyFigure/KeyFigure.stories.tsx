import type {Meta, StoryObj} from '@storybook/react';
import React from 'react';
import {KeyFigure, KeyFigureGrid} from './KeyFigure';
import * as Icons from '../../icons';
import {Tooltip} from '../Tooltip/Tooltip';

const meta: Meta<typeof KeyFigure> = {
  title: 'Components/KeyFigure',
  component: KeyFigure,
  argTypes: {
          icon: {
              control: {type: 'select'}, options: Object.keys(Icons),
              table: {type: {summary: 'ReactElement<IconProps>'}},
          },
      },
  args: {
          icon: 'TagIcon',
          title: 'Key figure with a multiple values',
      },
};

export default meta;
type Story = StoryObj<typeof meta>;

export const Standard: Story = {
  name: 'Standard',
  render: (args) => (
    <KeyFigureGrid>
          <KeyFigure icon={React.createElement(Icons[args.icon])} title={args.title}>
            <KeyFigure.Figure label="Average:">10</KeyFigure.Figure>
            <KeyFigure.Figure label="Max:">15</KeyFigure.Figure>
          </KeyFigure>
        </KeyFigureGrid>
  ),
};

export const ValueOnly: Story = {
  name: 'Value only',
  render: (args) => (
    <KeyFigureGrid>
          <KeyFigure icon={React.createElement(Icons[args.icon])} title="Key figure with only a figure">
            <KeyFigure.Figure>123</KeyFigure.Figure>
          </KeyFigure>
        </KeyFigureGrid>
  ),
};

export const KeyFigureGrid: Story = {
  name: 'KeyFigure grid',
  render: (args) => (
    <KeyFigureGrid>
          <KeyFigure icon={React.createElement(Icons[args.icon])} title="Key figure 1">
            <KeyFigure.Figure label="Average:">10</KeyFigure.Figure>
            <KeyFigure.Figure label="Max:">15</KeyFigure.Figure>
          </KeyFigure>
          <KeyFigure icon={React.createElement(Icons[args.icon])} title="Key figure 2 with a very very very very very long label">
            <KeyFigure.Figure>123</KeyFigure.Figure>
          </KeyFigure>
          <KeyFigure icon={React.createElement(Icons[args.icon])} title="Key figure 3">
            <KeyFigure.Figure>
              456
              <Tooltip iconSize={16}>More informations on this figure</Tooltip>
            </KeyFigure.Figure>
          </KeyFigure>
          <KeyFigure icon={React.createElement(Icons[args.icon])} title="Key figure 4">
            <KeyFigure.Figure label="Average:">789454</KeyFigure.Figure>
            <KeyFigure.Figure label="Max:">78945485</KeyFigure.Figure>
          </KeyFigure>
          <KeyFigure icon={React.createElement(Icons[args.icon])} title="Key figure 5">
            <KeyFigure.Figure label="Average:">5451</KeyFigure.Figure>
            <KeyFigure.Figure label="Max:">15369</KeyFigure.Figure>
          </KeyFigure>
        </KeyFigureGrid>
  ),
};

