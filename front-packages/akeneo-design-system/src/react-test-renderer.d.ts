declare module 'react-test-renderer' {
  import {ReactElement} from 'react';

  interface ReactTestRenderer {
    toJSON(): ReactTestRendererJSON | null;
    unmount(): void;
    update(element: ReactElement): void;
    root: ReactTestInstance;
  }

  interface ReactTestRendererJSON {
    type: string;
    props: Record<string, unknown>;
    children: Array<ReactTestRendererJSON | string> | null;
  }

  interface ReactTestInstance {
    type: string | Function;
    props: Record<string, unknown>;
    parent: ReactTestInstance | null;
    children: Array<ReactTestInstance | string>;
    find(predicate: (node: ReactTestInstance) => boolean): ReactTestInstance;
    findAll(predicate: (node: ReactTestInstance) => boolean): ReactTestInstance[];
    findByType(type: Function): ReactTestInstance;
    findAllByType(type: Function): ReactTestInstance[];
    findByProps(props: Record<string, unknown>): ReactTestInstance;
    findAllByProps(props: Record<string, unknown>): ReactTestInstance[];
  }

  export function create(element: ReactElement): ReactTestRenderer;
  export function act(callback: () => void | Promise<void>): void;
}
