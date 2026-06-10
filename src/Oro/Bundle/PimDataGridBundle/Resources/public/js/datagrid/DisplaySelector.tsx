import React, {useRef} from 'react';
import {useBooleanState} from 'akeneo-design-system';

type DisplayType = {
  label: string;
};

type DisplaySelectorProps = {
  types: {[name: string]: DisplayType};
  selectedType: string;
  displayLabel: string;
  onChange: (type: string) => void;
};

const DisplaySelector = ({types, selectedType, displayLabel, onChange}: DisplaySelectorProps) => {
  const [isOpen, open, close] = useBooleanState(false);
  const containerRef = useRef<HTMLDivElement>(null);

  const handleSelect = (type: string) => {
    close();
    onChange(type);
  };

  return (
    <div ref={containerRef} className={isOpen ? 'open' : ''}>
      {/* data-toggle is inert for React but is the contract used by Behat's
          getDropdownButton step ('*[data-toggle="dropdown"]:contains(...)') */}
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
            <li key={key} className="display-selector-item" data-type={key} onClick={() => handleSelect(key)}>
              <a
                className={`AknDropdown-menuLink${key === selectedType ? ' AknDropdown-menuLink--active' : ''}`}
                data-type={key}
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
