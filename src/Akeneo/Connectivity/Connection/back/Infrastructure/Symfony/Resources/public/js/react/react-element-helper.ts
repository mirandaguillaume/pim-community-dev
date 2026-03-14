import {createRoot, Root} from 'react-dom/client';

let container: Element | null = null;
let root: Root | null = null;

export const mountReactElementRef = (component: JSX.Element) => {
  if (null === container) {
    container = document.createElement('div');
    root = createRoot(container);
    root.render(component);
  }

  return container;
};

export const unmoundReactElementRef = () => {
  if (null !== root) {
    root.unmount();
    root = null;
    container = null;
  }
};
