import {IdentifierGenerator} from '../models';
import {InvalidIdentifierGenerator, ServerError} from '../errors';
import {useMutation, useQueryClient, UseMutateFunction} from '@tanstack/react-query';
import {useRouter} from '@akeneo-pim-community/shared';
import {Violation} from '../validators';

type ErrorResponse = {
  violations?: Violation[];
};

type HookResponse = {
  mutate: UseMutateFunction<IdentifierGenerator, ErrorResponse, IdentifierGenerator, unknown>;
  error: ErrorResponse;
  isPending: boolean;
};

const useCreateIdentifierGenerator = (): HookResponse => {
  const router = useRouter();
  const queryClient = useQueryClient();

  const {mutate, error, isPending} = useMutation<IdentifierGenerator, ErrorResponse, IdentifierGenerator>({
    mutationFn: async (generator: IdentifierGenerator) => {
      const response = await fetch(router.generate('akeneo_identifier_generator_rest_create'), {
        method: 'POST',
        headers: {'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest'},
        body: JSON.stringify(generator),
      });

      if (response.status === 400) {
        const data = await response.json();
        throw new InvalidIdentifierGenerator(data);
      }

      if (response.status !== 201) {
        throw new ServerError();
      }

      return await response.json();
    },
    onSuccess: () => queryClient.invalidateQueries({queryKey: ['getGeneratorList']}),
  });

  return {mutate, error: error ?? {}, isPending};
};

export {useCreateIdentifierGenerator};
