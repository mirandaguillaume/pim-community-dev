import React from 'react';
import {createRoot, Root} from 'react-dom/client';
import {ThemeProvider} from 'styled-components';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {pimTheme} from 'akeneo-design-system';
const StringCell = require('oro/datagrid/string-cell');
const requireContext = require('require-context');

const convertToType = (type: string, value: string) => {
  switch (type) {
    case 'boolean':
      return Boolean(value);
    case 'number':
      return Number(value);
    default:
      return value;
  }
};

type PropTypes = {[key: string]: string | number | boolean | (() => void)};

class ReactCell extends StringCell {
  private reactRoot: Root | null = null;

  render() {
    const {props: optionsProps, component} = this.options.column.attributes.extraOptions;
    const Component = requireContext(component).default;

    if (undefined === Component) {
      throw new Error(`Module ${component} has no default export`);
    }

    const props = Object.keys(optionsProps).reduce((props: PropTypes, key) => {
      props[key] = convertToType(optionsProps[key], this.model.get(key));

      return props;
    }, {});

    props.refreshCollection = () => this.model.collection.fetch();

    if (!this.reactRoot) {
      this.reactRoot = createRoot(this.el);
    }
    this.reactRoot.render(
      <ThemeProvider theme={pimTheme}>
        <DependenciesProvider>
          <Component {...props} />
        </DependenciesProvider>
      </ThemeProvider>
    );

    return this;
  }
}

export = ReactCell;
