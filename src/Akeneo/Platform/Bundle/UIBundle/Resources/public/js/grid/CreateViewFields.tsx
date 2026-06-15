import React, {useState} from 'react';

type FieldState = {
  label: string;
  isPrivate: boolean;
};

type Labels = {
  chooseLabel: string;
  placeholder: string;
  chooseType: string;
};

type Props = {
  labels: Labels;
  onChange: (state: FieldState) => void;
  onSubmit: () => void;
};

/**
 * Controlled React content of the "create a view" modal, rendered INSIDE the legacy
 * `Backbone.BootstrapModal` chrome (the shell keeps `.modal`/`.modal-body`/`.ok` so the
 * PIM-wide "fill in the popin" Behat step still works).
 *
 * Reproduces, byte-for-byte, the markup of `pim/template/grid/view-selector/create-view-inputs`:
 * the `input[name="new-view-label"]` field and the `.AknCreateView-typeSelector` public/private
 * toggle (both Behat-load-bearing). State (`label`, `isPrivate`) is owned here and lifted to the
 * Backbone shell via `onChange`; the shell toggles the modal's `.ok` button and builds the payload.
 */
const CreateViewFields = ({labels, onChange, onSubmit}: Props) => {
  const [label, setLabel] = useState('');
  const [isPrivate, setIsPrivate] = useState(true);

  const update = (nextLabel: string, nextIsPrivate: boolean) => {
    setLabel(nextLabel);
    setIsPrivate(nextIsPrivate);
    onChange({label: nextLabel, isPrivate: nextIsPrivate});
  };

  return (
    <>
      <div className="AknFieldContainer">
        <div className="AknFieldContainer-header">
          <label title={labels.chooseLabel} className="AknFieldContainer-label control-label required truncate">
            {labels.chooseLabel}
          </label>
        </div>
        <div className="AknFieldContainer-inputContainer field-input">
          <input
            name="new-view-label"
            type="text"
            className="AknTextField"
            placeholder={labels.placeholder}
            value={label}
            onChange={event => update(event.target.value, isPrivate)}
            onKeyPress={event => {
              if ('Enter' === event.key && label.length) {
                onSubmit();
              }
            }}
          />
        </div>
      </div>
      <div className="AknFieldContainer">
        <label title={labels.chooseType} className="AknFieldContainer-inputContainer AknFieldContainer--inline">
          <div
            className={`AknSelectButton ${isPrivate ? 'AknSelectButton--selected ' : ''}AknCreateView-typeSelector`}
            onClick={() => update(label, !isPrivate)}
          />
          <span className="AknFieldContainer-label--inline AknFieldContainer-label--right">{labels.chooseType}</span>
        </label>
      </div>
    </>
  );
};

export default CreateViewFields;
