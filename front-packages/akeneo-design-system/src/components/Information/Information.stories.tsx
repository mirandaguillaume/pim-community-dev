import React from 'react';
import LinkTo from '@storybook/addon-links/react';
import {Information, HighlightTitle} from './Information';
import {Link} from '../Link/Link';
import {UsersIllustration} from '../../illustrations';
import * as Illustrations from '../../illustrations';

export default {
  title: 'Components/Information',
  component: Information,

  argTypes: {
    illustration: {
      control: {
        type: 'select',
      },

      options: Object.keys(Illustrations),
    },

    title: {
      control: {
        type: 'text',
      },
    },

    children: {
      control: {
        type: 'text',
      },
    },
  },

  args: {
    illustration: 'UsersIllustration',
    title: 'Lorem ipsum.',
    children:
      'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
  },
};

export const Standard = {
  render: args => {
    const illustrationComponent = React.createElement(Illustrations[args.illustration]);
    return <Information {...args} illustration={illustrationComponent} />;
  },

  name: 'Standard',
};

export const Composition = {
  render: args => {
    const title = (
      <>
        Lorem ipsum <HighlightTitle>dolor</HighlightTitle>sit amet
      </>
    );

    return (
      <Information title={title} illustration={<UsersIllustration />}>
        Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore
        magna aliqua.
        <br />
        <Link target="_blank" href="https://www.akeneo.com/">
          Link to related lorem ipsum
        </Link>
      </Information>
    );
  },

  name: 'Composition',
};

export const FitContentHeight = {
  render: args => {
    const title = 'Lorem ipsum dolor sit amet';

    return (
      <Information title={title} illustration={<UsersIllustration />}>
        <p>
          Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore
          magna aliqua.
        </p>
        <p>
          Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore
          magna aliqua.
        </p>
        <p>
          Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore
          magna aliqua.
        </p>
        <p>
          Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore
          magna aliqua.
        </p>
        <p>
          Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore
          magna aliqua.
        </p>
        <p>
          Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore
          magna aliqua.
        </p>
        <p>
          Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore
          magna aliqua.
        </p>
      </Information>
    );
  },

  name: 'Fit content height',
};
