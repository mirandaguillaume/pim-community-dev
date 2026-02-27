import type {Meta, StoryObj} from '@storybook/react';
import React from 'react';
import {Information, HighlightTitle} from './Information';
import {Link} from '../Link/Link';
import {UsersIllustration} from '../../illustrations';
import * as Illustrations from '../../illustrations';

const meta: Meta<typeof Information> = {
  title: 'Components/Information',
  component: Information,
  argTypes: {
    illustration: {control: {type: 'select'}, options: Object.keys(Illustrations)},
    title: {control: {type: 'text'}},
    children: {control: {type: 'text'}}
  },
  args: {
    illustration: 'UsersIllustration',
    title: 'Lorem ipsum.',
    children: 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.'
  },
};

export default meta;
type Story = StoryObj<typeof meta>;

export const Standard: Story = {
  name: 'Standard',
  render: (args) => {
    const illustrationComponent = React.createElement(Illustrations[args.illustration]);
    return <Information {...args} illustration={illustrationComponent}/>;
  },
};

export const Composition: Story = {
  name: 'Composition',
  render: (args) => {
    const title = (<>Lorem ipsum <HighlightTitle>dolor</HighlightTitle> sit amet</>)
    return (
      <Information title={title} illustration={<UsersIllustration/>}>
        Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.
        <br/>
        <Link target="_blank" href="https://www.akeneo.com/">Link to related lorem ipsum</Link>
      </Information>
    );
  },
};

export const FitContentHeight: Story = {
  name: 'Fit content height',
  render: (args) => {
    const title = 'Lorem ipsum dolor sit amet';
    return (
      <Information title={title} illustration={<UsersIllustration/>}>
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
      </Information>
    );
  },
};

