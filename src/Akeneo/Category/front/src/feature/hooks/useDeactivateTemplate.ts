import {NotificationLevel, useNotify, useRoute, useTranslate} from '@akeneo-pim-community/shared';
import {apiFetch} from '../tools/apiFetch';
import {useMutation} from 'react-query';
import {useNavigate} from 'react-router-dom';

export const useDeactivateTemplate = (template: {id: string; label: string}) => {
  const navigate = useNavigate();
  const notify = useNotify();
  const translate = useTranslate();

  const url = useRoute('pim_enriched_category_rest_deactivate_template', {templateUuid: template.id});
  const mutation = useMutation(() => apiFetch(url, {method: 'DELETE'}));

  return async () => {
    try {
      await mutation.mutateAsync();
      notify(
        NotificationLevel.SUCCESS,
        translate('akeneo.category.template.deactivate.notification_success.title', {template: template.label}),
        translate('akeneo.category.template.deactivate.notification_success.message', {template: template.label})
      );
    } finally {
      navigate('/');
    }
  };
};
