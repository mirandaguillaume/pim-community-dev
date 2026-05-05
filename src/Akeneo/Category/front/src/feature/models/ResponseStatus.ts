export const DEACTIVATED_TEMPLATE = 'deactivated_template';
export const ATTRIBUTES_LIMIT_REACHED = 'attributes_limit_reached';

export enum ResponseStatusEnum {
  idle = 'idle',
  pending = 'pending',
  success = 'success',
  error = 'error',
}

export type ResponseStatus = 'idle' | 'pending' | 'success' | 'error';
