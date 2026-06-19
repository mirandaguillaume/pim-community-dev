import React, {useLayoutEffect} from 'react';

type Props = {
  // The Backbone view's this.el — filter elements are appended here directly
  // so the DOM depth stays identical to the previous imperative appendChild.
  container: HTMLElement;
  filterEls: HTMLElement[];
  onMounted: () => void;
};

const FiltersPanel: React.FC<Props> = ({container, filterEls, onMounted}) => {
  useLayoutEffect(() => {
    filterEls.forEach(el => container.appendChild(el));
    onMounted();
  }, [filterEls]);

  return null;
};

export default FiltersPanel;
