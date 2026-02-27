import { EffectCallback } from 'react';
declare const useUnsavedChanges: <ValueType>(entity: ValueType, beforeUnloadMessage: string) => [boolean, EffectCallback];
export { useUnsavedChanges };
