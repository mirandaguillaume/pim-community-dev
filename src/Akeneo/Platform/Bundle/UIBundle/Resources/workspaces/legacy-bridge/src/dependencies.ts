import {DependenciesContextProps, systemConfiguration} from '@akeneo-pim-community/shared';

/* eslint-disable @typescript-eslint/no-var-requires */
import router from 'pim/router';
import translate from 'oro/translator';
import viewBuilder from 'pim/form-builder';
import * as messenger from 'oro/messenger';
import userContext from 'pim/user-context';
import securityContext from 'pim/security-context';
import mediator from 'oro/mediator';
import featureFlags from 'pim/feature-flags';
import analytics from 'pim/analytics';

const dependencies: DependenciesContextProps = {
  router,
  translate,
  viewBuilder,
  notify: messenger.notify,
  user: userContext,
  security: {
    isGranted: securityContext.isGranted.bind(securityContext),
  },
  mediator,
  featureFlags,
  analytics,
  systemConfiguration,
};

export {dependencies};
