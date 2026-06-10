import React from 'react';
import {useBooleanState} from 'akeneo-design-system';

type DisplayType = {
  label: string;
};

type DisplaySelectorProps = {
  types: {[name: string]: DisplayType};
  selectedType: string;
  displayLabel: string;
};

// Item clicks are handled by jQuery delegation in display-selector.tsx (events hash).
// React only manages the open/close toggle; this avoids a race between
// Routing.reloadPage() (called synchronously inside the jQuery handler) and React's
// pending close() state flush which would unmount the tree mid-navigation.
const DisplaySelector = ({types, selectedType, displayLabel}: DisplaySelectorProps) => {
  const [isOpen, open, close] = useBooleanState(false);

  return (
    <div className={isOpen ? 'open' : ''}>
      {/* data-toggle is the contract used by Behat's getDropdownButton step */}
      <div
        className="AknActionButton AknActionButton--withoutBorder"
        data-toggle="dropdown"
        onClick={isOpen ? close : open}
      >
        {displayLabel}: <span className="AknActionButton-highlight">{types[selectedType]?.label}</span>
        <span className="AknActionButton-caret" />
      </div>
      {isOpen && (
        <ul className="AknDropdown-menu AknDropdown-menu--open">
          <div className="AknDropdown-menuTitle">{displayLabel}</div>
          {Object.entries(types).map(([key, type]) => (
            <li key={key} className="display-selector-item" data-type={key}>
              <a
                className={`AknDropdown-menuLink${key === selectedType ? ' AknDropdown-menuLink--active' : ''}`}
                data-type={key}
                role="button"
              >
                {type.label}
              </a>
            </li>
          ))}
        </ul>
      )}
    </div>
  );
};

export {DisplaySelector};
export type {DisplaySelectorProps};
