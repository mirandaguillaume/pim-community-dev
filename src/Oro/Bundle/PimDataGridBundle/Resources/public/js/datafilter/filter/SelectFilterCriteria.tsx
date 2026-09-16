import React from 'react';
import {SelectInput, MultiSelectInput} from 'akeneo-design-system';

type Choice = {value: string; label: string};

type Props = {
  multiple: boolean;
  value: string[];
  choices: Choice[];
  showLabel: boolean;
  label: string;
  canDisable: boolean;
  nullLink: string;
  placeholder: string;
  emptyResultLabel: string;
  openLabel: string;
  removeLabel: string;
  onChange: (values: string[]) => void;
  onDisable: () => void;
};

/**
 * Controlled React view of the `select` family of datagrid filters (Vague B). Replaces the legacy
 * `jquery.multiselect` widget with the in-house DSM `SelectInput` (single) / `MultiSelectInput` (multi).
 *
 * The internal value is ALWAYS `string[]` so the multi bridge reuses this unchanged; the single branch
 * maps `SelectInput`'s `null`↔`[]` and `v`↔`[v]`. The `.filter-select` wrapper keeps the Behat entry
 * class and adds `data-testid="select-filter-widget"`; `MultiSelectInput.Option`s carry
 * `data-testid={value}` (`SelectInput` stamps it itself). The DSM overlay is a React portal, so the
 * bridge's `ReactDOM.unmountComponentAtNode` tears it down — no jQuery-orphan cleanup.
 *
 * The `.disable-filter` link is a SIBLING of `.filter-select` (mirrors the legacy `select-filter` template).
 */
const SelectFilterCriteria = ({
  multiple,
  value,
  choices,
  showLabel,
  label,
  canDisable,
  nullLink,
  placeholder,
  emptyResultLabel,
  openLabel,
  removeLabel,
  onChange,
  onDisable,
}: Props) => (
  <>
    <div className="AknFilterBox-filter filter-select filter-criteria-selector" data-testid="select-filter-widget">
      {showLabel && <span className="AknFilterBox-filterLabel">{label}</span>}
      {multiple ? (
        <MultiSelectInput
          value={value}
          onChange={onChange}
          placeholder={placeholder}
          emptyResultLabel={emptyResultLabel}
          openLabel={openLabel}
          removeLabel={removeLabel}
        >
          {choices.map(choice => (
            <MultiSelectInput.Option key={choice.value} value={choice.value} data-testid={choice.value}>
              {choice.label}
            </MultiSelectInput.Option>
          ))}
        </MultiSelectInput>
      ) : (
        <SelectInput
          clearable
          value={value[0] ?? null}
          onChange={(newValue: string | null) => onChange(newValue === null ? [] : [newValue])}
          placeholder={placeholder}
          emptyResultLabel={emptyResultLabel}
          openLabel={openLabel}
        >
          {choices.map(choice => (
            <SelectInput.Option key={choice.value} value={choice.value}>
              {choice.label}
            </SelectInput.Option>
          ))}
        </SelectInput>
      )}
    </div>
    {canDisable && (
      <a
        href={nullLink}
        className="AknFilterBox-disableFilter AknIconButton AknIconButton--remove disable-filter"
        onClick={event => {
          event.preventDefault();
          onDisable();
        }}
      />
    )}
  </>
);

export default SelectFilterCriteria;
