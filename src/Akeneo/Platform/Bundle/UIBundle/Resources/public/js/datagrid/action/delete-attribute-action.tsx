import React from 'react';
import {createRoot} from 'react-dom/client';
import {flushSync} from 'react-dom';
import {DeleteModal} from 'pimui/js/attribute/form/delete/DeleteModal';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';

const DeleteAction = require('oro/datagrid/delete-action');
const mediator = require('oro/mediator');

class DeleteAttributeAction extends DeleteAction {
  execute() {
    const container = document.createElement('div');
    document.body.appendChild(container);
    const root = createRoot(container);

    const handleClose = () => {
      root.unmount();
      document.body.removeChild(container);
    };

    const handleSuccess = () => {
      mediator.trigger('datagrid:doRefresh:' + this.datagrid.name);

      handleClose();
    };

    flushSync(() => {
      root.render(
        <DependenciesProvider>
          <ThemeProvider theme={pimTheme}>
            <DeleteModal onCancel={handleClose} onSuccess={handleSuccess} attributeCode={this.model.attributes.code} />
          </ThemeProvider>
        </DependenciesProvider>
      );
    });
  }
}

export = DeleteAttributeAction;
