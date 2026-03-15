import React from 'react';
import {createRoot} from 'react-dom/client';
import {flushSync} from 'react-dom';
import {DuplicateUserApp} from '@akeneo-pim-community/user-ui';

const Routing = require('pim/router');
const AbstractAction = require('oro/datagrid/abstract-action');

class DuplicateUserAction extends AbstractAction {
  /**
   * {@inheritdoc}
   */
  execute() {
    const container = document.createElement('div');
    document.body.appendChild(container);
    const root = createRoot(container);

    const closeApp = () => {
      root.unmount();
      document.body.removeChild(container);
    };
    const onDuplicateSuccess = (duplicatedUserId: number) => {
      closeApp();
      Routing.redirect(Routing.generate('pim_user_edit', {identifier: duplicatedUserId}));
    };

    flushSync(() => {
      root.render(
        <DuplicateUserApp
          userId={this.model.get('id')}
          userCode={this.model.get('username')}
          onCancel={closeApp}
          onDuplicateSuccess={onDuplicateSuccess}
        />
      );
    });
  }
}

export = DuplicateUserAction;
