import {useMutation, UseMutateFunction} from '@tanstack/react-query';
import {useRouter} from '@akeneo-pim-community/shared';
import {IdentifierGenerator} from '../models';
import {Violation} from '../validators';

type HookResponse = {
  isLoading: boolean;
  save: UseMutateFunction<IdentifierGenerator, Violation[], IdentifierGenerator>;
  error: Violation[];
};

const useSaveGenerator = (): HookResponse => {
  const router = useRouter();

  const callSave = async (generator: IdentifierGenerator) => {
    const res = await fetch(router.generate('akeneo_identifier_generator_rest_update', {code: generator.code}), {
      method: 'PATCH',
      headers: [
        ['Content-type', 'application/json'],
        ['X-Requested-With', 'XMLHttpRequest'],
      ],
      body: JSON.stringify(generator),
    });
    const data = await res.json();

    return res.ok ? data : Promise.reject(data);
  };

  const {mutate, isPending, error} = useMutation<IdentifierGenerator, Violation[], IdentifierGenerator>({
    mutationFn: callSave,
  });

  return {isLoading: isPending, save: mutate, error: error ?? []};
};

export {useSaveGenerator};
