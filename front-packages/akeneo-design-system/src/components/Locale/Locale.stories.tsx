import React from 'react';
import {Locale} from './Locale';

export default {
  title: 'Components/Locale',
  component: Locale,

  args: {
    code: 'en_US',
  },
};

export const Standard = {
  render: args => {
    return <Locale {...args} />;
  },

  name: 'Standard',
};

export const Label = {
  render: args => {
    return (
      <>
        <Locale code="en_US" languageLabel="English" />
        <Locale code="fr_FR" languageLabel="Français" />
        <Locale code="de_DE" />
        <Locale code="pt_BR" />
      </>
    );
  },

  name: 'Label',
};
