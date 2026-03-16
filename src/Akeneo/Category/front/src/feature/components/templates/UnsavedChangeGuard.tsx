import {useTranslate} from '@akeneo-pim-community/shared';
import {useCallback, useContext, useEffect} from 'react';
import {useBlocker} from 'react-router-dom';
import {useSaveStatus} from '../../hooks/useSaveStatus';
import {CanLeavePageContext} from '../providers';
import {Status} from '../providers/SaveStatusProvider';

export const UnsavedChangesGuard = () => {
  const translate = useTranslate();

  const {globalStatus} = useSaveStatus();
  const hasUnsavedChanges = globalStatus !== Status.SAVED;

  // Browser
  const handleBeforeUnload = useCallback(
    (event: BeforeUnloadEvent) => {
      if (hasUnsavedChanges) {
        event.preventDefault();
        event.returnValue = translate('akeneo.category.template.attribute.settings.unsaved_changes');
      }
    },
    [hasUnsavedChanges, translate]
  );
  useEffect(() => {
    window.addEventListener('beforeunload', handleBeforeUnload);
    return () => {
      window.removeEventListener('beforeunload', handleBeforeUnload);
    };
  }, [handleBeforeUnload]);

  // Backbone
  const {setCanLeavePage, setLeavePageMessage} = useContext(CanLeavePageContext);
  useEffect(() => {
    if (!hasUnsavedChanges) {
      setCanLeavePage(true);
    } else {
      setCanLeavePage(false);
      setLeavePageMessage(translate('akeneo.category.template.attribute.settings.unsaved_changes'));
    }
  }, [hasUnsavedChanges, setCanLeavePage, setLeavePageMessage, translate]);

  // React-router — block in-app navigation when there are unsaved changes
  const blocker = useBlocker(hasUnsavedChanges);
  useEffect(() => {
    if (blocker.state === 'blocked') {
      const message = translate('akeneo.category.template.attribute.settings.unsaved_changes');
      if (window.confirm(message)) {
        blocker.proceed();
      } else {
        blocker.reset();
      }
    }
  }, [blocker, translate]);

  return null;
};
