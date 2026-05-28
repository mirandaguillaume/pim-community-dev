import {Nomenclature} from '../models';
import {useMutation, useQueryClient, UseMutateFunction} from '@tanstack/react-query';
import {useRouter} from '@akeneo-pim-community/shared';
import {Violation} from '../validators';

const useSaveNomenclature: () => {
  save: UseMutateFunction<void, Violation[], Nomenclature>;
  isLoading: boolean;
} = () => {
  const router = useRouter();
  const queryClient = useQueryClient();

  const {mutate: save, isPending} = useMutation<void, Violation[], Nomenclature>({
    mutationFn: async (nomenclature: Nomenclature) => {
      const response = await fetch(
        router.generate('akeneo_identifier_generator_nomenclature_rest_update', {
          propertyCode: nomenclature.propertyCode,
        }),
        {
          method: 'PATCH',
          headers: [
            ['Content-type', 'application/json'],
            ['X-Requested-With', 'XMLHttpRequest'],
          ],
          body: JSON.stringify(nomenclature),
        }
      );

      const data = await response.json();

      return response.ok ? data : Promise.reject(data);
    },
    onSuccess: () => queryClient.invalidateQueries({queryKey: ['getNomenclature']}),
  });

  return {save, isLoading: isPending};
};

export {useSaveNomenclature};
