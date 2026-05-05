import React from 'react';
import {NotificationLevel, useNotify, useRouter, useTranslate} from '@akeneo-pim-community/shared';
import {useQuery} from '@tanstack/react-query';
import {useNavigate} from 'react-router-dom';
import {Template} from '../models';
import {apiFetch} from '../tools/apiFetch';

export const useTemplateByTemplateUuid = (uuid: string | null) => {
  const router = useRouter();
  const translate = useTranslate();
  const notify = useNotify();
  const navigate = useNavigate();

  const url = router.generate('pim_category_template_rest_get_by_template_uuid', {
    templateUuid: uuid,
  });

  const query = useQuery({
    queryKey: ['get-template', uuid],
    queryFn: () => apiFetch<Template>(url, {}),
    enabled: null !== uuid,
  });

  React.useEffect(() => {
    if (query.isError) {
      navigate('/');
      notify(NotificationLevel.ERROR, translate('akeneo.category.template.not_found'));
    }
  }, [query.isError, navigate, notify, translate]);

  return query;
};
