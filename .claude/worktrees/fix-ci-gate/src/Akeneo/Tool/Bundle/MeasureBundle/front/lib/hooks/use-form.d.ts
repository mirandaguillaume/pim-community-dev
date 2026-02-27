declare type SetValue = (path: string, value: any) => void;
declare type ClearValues = () => void;
declare const useForm: <T>(defaultValues: T) => [T, SetValue, ClearValues];
export { useForm };
