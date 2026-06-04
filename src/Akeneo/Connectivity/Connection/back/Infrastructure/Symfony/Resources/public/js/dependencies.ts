import router from 'pim/router';
import translate from 'oro/translator';
import viewBuilder from 'pim/form-builder';
import * as messenger from 'oro/messenger';
import userContext from 'pim/user-context';
import securityContext from 'pim/security-context';
import featureFlags from 'pim/feature-flags';
import permissionFormRegistry from 'pim/permission-form-registry';

export const dependencies = {
  router,
  translate,
  viewBuilder,
  notify: messenger.notify,
  user: userContext,
  security: {
    isGranted: securityContext.isGranted.bind(securityContext),
  },
  featureFlags,
  permissionFormRegistry: permissionFormRegistry,
};
