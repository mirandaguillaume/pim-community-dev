import React from 'react';
import {fireEvent, screen, act} from '@testing-library/react';
import {OperationCollection} from './OperationCollection';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {ConfigContext} from '../../context/config-context';

const renderWithConfig = (ui: React.ReactElement, config = {operations_max: 5, units_max: 50, families_max: 100}) => {
  return renderWithProviders(<ConfigContext.Provider value={config}>{ui}</ConfigContext.Provider>);
};

// ─── Rendering ─────────────────────────────────────────────────────────────────

test('It renders the given operations with correct values and operators', () => {
  const operations = [
    {value: '12', operator: 'div'},
    {value: '25', operator: 'mul'},
    {value: '54', operator: 'add'},
  ];

  renderWithConfig(<OperationCollection operations={operations} onOperationsChange={() => {}} />);

  const valueInputs = screen.getAllByPlaceholderText('measurements.unit.operation.placeholder') as HTMLInputElement[];

  expect(valueInputs).toHaveLength(3);
  expect(valueInputs[0].value).toBe('12');
  expect(valueInputs[1].value).toBe('25');
  expect(valueInputs[2].value).toBe('54');
  expect(screen.getByText('measurements.unit.operator.div')).toBeInTheDocument();
  expect(screen.getByText('measurements.unit.operator.mul')).toBeInTheDocument();
  expect(screen.getByText('measurements.unit.operator.add')).toBeInTheDocument();
});

test('It renders the label with correct translation keys', () => {
  renderWithConfig(<OperationCollection operations={[{value: '1', operator: 'mul'}]} onOperationsChange={() => {}} />);

  expect(screen.getByText(/measurements\.unit\.convert_from_standard/)).toBeInTheDocument();
  expect(screen.getByText(/pim_common\.required_label/)).toBeInTheDocument();
});

test('It renders the placeholder text with the correct translation key', () => {
  renderWithConfig(<OperationCollection operations={[{value: '', operator: 'mul'}]} onOperationsChange={() => {}} />);

  expect(screen.getByPlaceholderText('measurements.unit.operation.placeholder')).toBeInTheDocument();
});

test('It renders empty operations array with just the add button', () => {
  renderWithConfig(<OperationCollection operations={[]} onOperationsChange={jest.fn()} />);

  expect(screen.queryByPlaceholderText('measurements.unit.operation.placeholder')).not.toBeInTheDocument();
  expect(screen.getByText('measurements.unit.operation.add')).toBeInTheDocument();
});

test('It renders a single operation without remove button', () => {
  renderWithConfig(<OperationCollection operations={[{value: '42', operator: 'mul'}]} onOperationsChange={() => {}} />);

  expect(screen.queryByTitle('pim_common.remove')).not.toBeInTheDocument();
  const valueInputs = screen.getAllByPlaceholderText('measurements.unit.operation.placeholder') as HTMLInputElement[];
  expect(valueInputs).toHaveLength(1);
  expect(valueInputs[0].value).toBe('42');
});

test('Multiple operations render with the correct number of inputs and remove buttons', () => {
  const operations = [
    {value: '12', operator: 'div'},
    {value: '25', operator: 'add'},
    {value: '54', operator: 'mul'},
  ];

  renderWithConfig(<OperationCollection operations={operations} onOperationsChange={() => {}} />);

  expect(screen.getAllByPlaceholderText('measurements.unit.operation.placeholder')).toHaveLength(3);
  expect(screen.getAllByTitle('pim_common.remove')).toHaveLength(3);
});

// ─── Add Operation ─────────────────────────────────────────────────────────────

test('I can add an operation with the correct default values', () => {
  const operations = [
    {value: '12', operator: 'div'},
    {value: '25', operator: 'add'},
  ];
  const onOperationsChange = jest.fn();

  renderWithConfig(<OperationCollection operations={operations} onOperationsChange={onOperationsChange} />);

  fireEvent.click(screen.getByText('measurements.unit.operation.add'));

  expect(onOperationsChange).toHaveBeenCalledTimes(1);
  expect(onOperationsChange).toHaveBeenCalledWith([
    {value: '12', operator: 'div'},
    {value: '25', operator: 'add'},
    {value: '', operator: 'mul'},
  ]);
});

test('The add button is disabled when operations reach the max limit', () => {
  const operations = [
    {value: '1', operator: 'mul'},
    {value: '2', operator: 'div'},
    {value: '3', operator: 'add'},
  ];

  renderWithConfig(<OperationCollection operations={operations} onOperationsChange={jest.fn()} />, {
    operations_max: 3,
    units_max: 50,
    families_max: 100,
  });

  const addButton = screen.getByText('measurements.unit.operation.add');
  expect(addButton.closest('button')).toBeDisabled();
});

test('The add button is enabled when operations are below the max limit', () => {
  const operations = [
    {value: '1', operator: 'mul'},
    {value: '2', operator: 'div'},
  ];

  renderWithConfig(<OperationCollection operations={operations} onOperationsChange={jest.fn()} />, {
    operations_max: 5,
    units_max: 50,
    families_max: 100,
  });

  const addButton = screen.getByText('measurements.unit.operation.add');
  expect(addButton.closest('button')).not.toBeDisabled();
});

// ─── Remove Operation ──────────────────────────────────────────────────────────

test('I can remove the first operation', () => {
  const operations = [
    {value: '12', operator: 'div'},
    {value: '25', operator: 'add'},
  ];
  const onOperationsChange = jest.fn();

  renderWithConfig(<OperationCollection operations={operations} onOperationsChange={onOperationsChange} />);

  fireEvent.click(screen.getAllByTitle('pim_common.remove')[0]);

  expect(onOperationsChange).toHaveBeenCalledTimes(1);
  expect(onOperationsChange).toHaveBeenCalledWith([{value: '25', operator: 'add'}]);
});

test('I can remove the second operation', () => {
  const operations = [
    {value: '12', operator: 'div'},
    {value: '25', operator: 'add'},
  ];
  const onOperationsChange = jest.fn();

  renderWithConfig(<OperationCollection operations={operations} onOperationsChange={onOperationsChange} />);

  fireEvent.click(screen.getAllByTitle('pim_common.remove')[1]);

  expect(onOperationsChange).toHaveBeenCalledWith([{value: '12', operator: 'div'}]);
});

test('I cannot remove an operation when there is only one', () => {
  renderWithConfig(<OperationCollection operations={[{value: '12', operator: 'div'}]} onOperationsChange={() => {}} />);

  expect(screen.queryByTitle('pim_common.remove')).not.toBeInTheDocument();
});

test('Removing an operation also closes the operator selector', () => {
  const operations = [
    {value: '12', operator: 'div'},
    {value: '25', operator: 'add'},
  ];
  const onOperationsChange = jest.fn();

  renderWithConfig(<OperationCollection operations={operations} onOperationsChange={onOperationsChange} />);

  fireEvent.click(screen.getByText('measurements.unit.operator.div'));
  expect(screen.getByText('measurements.unit.operator.select')).toBeInTheDocument();

  fireEvent.click(screen.getAllByTitle('pim_common.remove')[0]);

  expect(onOperationsChange).toHaveBeenCalledWith([{value: '25', operator: 'add'}]);
});

// ─── Edit Value ────────────────────────────────────────────────────────────────

test('I can edit the value of the first operation', () => {
  const operations = [
    {value: '12', operator: 'div'},
    {value: '25', operator: 'add'},
  ];
  const onOperationsChange = jest.fn();

  renderWithConfig(<OperationCollection operations={operations} onOperationsChange={onOperationsChange} />);

  fireEvent.change(screen.getAllByPlaceholderText('measurements.unit.operation.placeholder')[0], {
    target: {value: '23'},
  });

  expect(onOperationsChange).toHaveBeenCalledTimes(1);
  expect(onOperationsChange).toHaveBeenCalledWith([
    {value: '23', operator: 'div'},
    {value: '25', operator: 'add'},
  ]);
});

test('I can edit the value of the second operation', () => {
  const operations = [
    {value: '12', operator: 'div'},
    {value: '25', operator: 'add'},
  ];
  const onOperationsChange = jest.fn();

  renderWithConfig(<OperationCollection operations={operations} onOperationsChange={onOperationsChange} />);

  fireEvent.change(screen.getAllByPlaceholderText('measurements.unit.operation.placeholder')[1], {
    target: {value: '99'},
  });

  expect(onOperationsChange).toHaveBeenCalledTimes(1);
  expect(onOperationsChange).toHaveBeenCalledWith([
    {value: '12', operator: 'div'},
    {value: '99', operator: 'add'},
  ]);
});

// ─── Operator Selector ─────────────────────────────────────────────────────────

test('I can change the operator of the first operation', () => {
  const operations = [
    {value: '12', operator: 'div'},
    {value: '25', operator: 'add'},
  ];
  const onOperationsChange = jest.fn();

  renderWithConfig(<OperationCollection operations={operations} onOperationsChange={onOperationsChange} />);

  fireEvent.click(screen.getByText('measurements.unit.operator.div'));
  expect(screen.getByText('measurements.unit.operator.select')).toBeInTheDocument();

  fireEvent.click(screen.getByText('measurements.unit.operator.sub'));

  expect(onOperationsChange).toHaveBeenCalledTimes(1);
  expect(onOperationsChange).toHaveBeenCalledWith([
    {value: '12', operator: 'sub'},
    {value: '25', operator: 'add'},
  ]);
});

test('I can change the operator of the second operation', () => {
  const operations = [
    {value: '12', operator: 'div'},
    {value: '25', operator: 'add'},
  ];
  const onOperationsChange = jest.fn();

  renderWithConfig(<OperationCollection operations={operations} onOperationsChange={onOperationsChange} />);

  fireEvent.click(screen.getByText('measurements.unit.operator.add'));

  fireEvent.click(screen.getByText('measurements.unit.operator.mul'));

  expect(onOperationsChange).toHaveBeenCalledWith([
    {value: '12', operator: 'div'},
    {value: '25', operator: 'mul'},
  ]);
});

test('The operator selector shows all four operators with correct translation keys', () => {
  const operations = [{value: '12', operator: 'div'}];

  renderWithConfig(<OperationCollection operations={operations} onOperationsChange={() => {}} />);

  fireEvent.click(screen.getByText('measurements.unit.operator.div'));

  expect(screen.getByText('measurements.unit.operator.mul')).toBeInTheDocument();
  expect(screen.getAllByText('measurements.unit.operator.div')).toHaveLength(2); // button + selector
  expect(screen.getByText('measurements.unit.operator.add')).toBeInTheDocument();
  expect(screen.getByText('measurements.unit.operator.sub')).toBeInTheDocument();
  expect(screen.getByText('measurements.unit.operator.select')).toBeInTheDocument();
});

test('The operator selector closes when clicking the mask', () => {
  const operations = [{value: '12', operator: 'div'}];

  renderWithConfig(<OperationCollection operations={operations} onOperationsChange={() => {}} />);

  fireEvent.click(screen.getByText('measurements.unit.operator.div'));
  expect(screen.getByText('measurements.unit.operator.select')).toBeInTheDocument();

  // The OperatorSelectorMask is a fixed-position overlay rendered as a sibling
  // before the OperatorSelector. Find the OperatorSelector (parent of the label)
  // and then get its previousElementSibling which is the mask.
  const selectorLabel = screen.getByText('measurements.unit.operator.select');
  // selectorLabel is inside OperatorSelectorLabel inside OperatorSelector
  // Go up to find the OperatorSelector div (grandparent of the label text)
  const operatorSelector = selectorLabel.parentElement!;
  const mask = operatorSelector.previousElementSibling!;
  fireEvent.click(mask);

  expect(screen.queryByText('measurements.unit.operator.select')).not.toBeInTheDocument();
});

test('The operator selector closes after selecting an operator', () => {
  const operations = [{value: '12', operator: 'div'}];

  renderWithConfig(<OperationCollection operations={operations} onOperationsChange={jest.fn()} />);

  fireEvent.click(screen.getByText('measurements.unit.operator.div'));
  expect(screen.getByText('measurements.unit.operator.select')).toBeInTheDocument();

  fireEvent.click(screen.getByText('measurements.unit.operator.mul'));

  expect(screen.queryByText('measurements.unit.operator.select')).not.toBeInTheDocument();
});

test('Selecting the same operator that is already selected still calls onOperationsChange', () => {
  const operations = [{value: '12', operator: 'div'}];
  const onOperationsChange = jest.fn();

  renderWithConfig(<OperationCollection operations={operations} onOperationsChange={onOperationsChange} />);

  fireEvent.click(screen.getByText('measurements.unit.operator.div'));
  fireEvent.click(screen.getAllByText('measurements.unit.operator.div')[1]);

  expect(onOperationsChange).toHaveBeenCalledWith([{value: '12', operator: 'div'}]);
});

// ─── isSelected styling on operator options (kills line 225 mutants) ────────

test('The currently selected operator option has isSelected styling applied', () => {
  const operations = [{value: '12', operator: 'mul'}];

  renderWithConfig(<OperationCollection operations={operations} onOperationsChange={jest.fn()} />);

  // Open selector
  fireEvent.click(screen.getByText('measurements.unit.operator.mul'));

  // Get the selected option (mul) and a non-selected option (div) in the selector
  const mulOption = screen.getAllByText('measurements.unit.operator.mul')[1]; // second one is in selector
  const divOption = screen.getByText('measurements.unit.operator.div');

  // The selected operator should have italic font-style and bold font-weight
  // styled-components injects <style> tags that are picked up by getComputedStyle
  const mulStyle = window.getComputedStyle(mulOption.closest('[class]')!);
  const divStyle = window.getComputedStyle(divOption.closest('[class]')!);

  expect(mulStyle.fontStyle).toBe('italic');
  expect(mulStyle.fontWeight).toBe('bold');
  // Non-selected should NOT have italic
  expect(divStyle.fontStyle).not.toBe('italic');
});

test('isSelected is false for non-matching operators and true only for the current one', () => {
  const operations = [{value: '12', operator: 'sub'}];

  renderWithConfig(<OperationCollection operations={operations} onOperationsChange={jest.fn()} />);

  fireEvent.click(screen.getByText('measurements.unit.operator.sub'));

  // The sub option in the selector should be styled as selected
  const subOptions = screen.getAllByText('measurements.unit.operator.sub');
  const subOptionInSelector = subOptions[1]; // second occurrence is in selector
  const mulOption = screen.getByText('measurements.unit.operator.mul');

  const subStyle = window.getComputedStyle(subOptionInSelector.closest('[class]')!);
  const mulStyle = window.getComputedStyle(mulOption.closest('[class]')!);

  expect(subStyle.fontStyle).toBe('italic');
  expect(subStyle.fontWeight).toBe('bold');
  expect(mulStyle.fontStyle).not.toBe('italic');
});

// ─── hasOffset on error helpers (kills line 242 mutants) ────────────────────

test('Error on the first operation (index=0) renders without offset', () => {
  const operations = [
    {value: '', operator: 'mul'},
    {value: '25', operator: 'div'},
  ];

  const {container} = renderWithConfig(
    <OperationCollection
      operations={operations}
      onOperationsChange={() => {}}
      errors={[{propertyPath: '[0]', message: 'first error', messageTemplate: 'first error', parameters: {}}]}
    />
  );

  const errorHelper = screen.getByText('first error');
  // The SpacedHelper wrapper for index 0 should have hasOffset=false (0 < 0 is false)
  // which means margin-left: 0px
  const helperWrapper = errorHelper.closest('[class*="SpacedHelper"]') || errorHelper.parentElement!;
  const style = window.getComputedStyle(helperWrapper);
  // With hasOffset=false, margin-left should be 0
  expect(style.marginLeft).toBe('0px');
});

test('Error on the second operation (index=1) renders with offset', () => {
  const operations = [
    {value: '12', operator: 'mul'},
    {value: '', operator: 'div'},
  ];

  const {container} = renderWithConfig(
    <OperationCollection
      operations={operations}
      onOperationsChange={() => {}}
      errors={[{propertyPath: '[1]', message: 'second error', messageTemplate: 'second error', parameters: {}}]}
    />
  );

  const errorHelper = screen.getByText('second error');
  const helperWrapper = errorHelper.closest('[class*="SpacedHelper"]') || errorHelper.parentElement!;
  const style = window.getComputedStyle(helperWrapper);
  // With hasOffset=true (0 < 1), margin-left should be 24px
  expect(style.marginLeft).toBe('24px');
});

test('Errors on index 0 and index 1 have different offset styling', () => {
  const operations = [
    {value: '', operator: 'mul'},
    {value: '', operator: 'div'},
  ];

  renderWithConfig(
    <OperationCollection
      operations={operations}
      onOperationsChange={() => {}}
      errors={[
        {propertyPath: '[0]', message: 'error-zero', messageTemplate: 'error-zero', parameters: {}},
        {propertyPath: '[1]', message: 'error-one', messageTemplate: 'error-one', parameters: {}},
      ]}
    />
  );

  const errorZero = screen.getByText('error-zero');
  const errorOne = screen.getByText('error-one');

  // The wrapping SpacedHelper elements should have different class names
  // due to different hasOffset prop values (false for index 0, true for index 1)
  const wrapperZero = errorZero.parentElement!;
  const wrapperOne = errorOne.parentElement!;

  // Check that the actual injected CSS reflects different margin-left values
  // by examining the style sheets
  const sheets = Array.from(document.querySelectorAll('style[data-styled]'));
  const cssText = sheets.map(s => s.textContent).join('\n');

  // The CSS should contain both margin-left:0px and margin-left:24px
  expect(cssText).toContain('margin-left:0px');
  expect(cssText).toContain('margin-left:24px');
});

// ─── shouldHideErrors behavior (kills line 207 and line 262 mutants) ────────

test('Indexed operation errors are hidden immediately after removing an operation', () => {
  const operations = [
    {value: '12', operator: 'div'},
    {value: '', operator: 'mul'},
  ];
  const onOperationsChange = jest.fn();

  const errors = [{propertyPath: '[1]', message: 'value required', messageTemplate: 'value required', parameters: {}}];

  renderWithConfig(
    <OperationCollection operations={operations} onOperationsChange={onOperationsChange} errors={errors} />
  );

  // Error should be visible initially
  expect(screen.getByText('value required')).toBeInTheDocument();

  // Remove the second operation - triggers setShouldHideErrors(true) synchronously
  fireEvent.click(screen.getAllByTitle('pim_common.remove')[1]);

  expect(onOperationsChange).toHaveBeenCalledWith([{value: '12', operator: 'div'}]);

  // Errors should be hidden immediately because setShouldHideErrors(true) was called
  expect(screen.queryByText('value required')).not.toBeInTheDocument();
});

test('Errors reappear when errors array length changes after being hidden', () => {
  const operations = [
    {value: '12', operator: 'div'},
    {value: '', operator: 'mul'},
  ];
  const onOperationsChange = jest.fn();

  const errors = [{propertyPath: '[1]', message: 'value required', messageTemplate: 'value required', parameters: {}}];

  const {rerender} = renderWithConfig(
    <OperationCollection operations={operations} onOperationsChange={onOperationsChange} errors={errors} />
  );

  expect(screen.getByText('value required')).toBeInTheDocument();

  // Remove triggers shouldHideErrors = true
  fireEvent.click(screen.getAllByTitle('pim_common.remove')[1]);

  // Re-render with new errors (different length) - should reset shouldHideErrors
  const newErrors = [
    {propertyPath: '[0]', message: 'new error', messageTemplate: 'new error', parameters: {}},
    {propertyPath: '', message: 'global err', messageTemplate: 'global err', parameters: {}},
  ];

  rerender(
    <ConfigContext.Provider value={{operations_max: 5, units_max: 50, families_max: 100}}>
      <OperationCollection
        operations={[{value: '12', operator: 'div'}]}
        onOperationsChange={onOperationsChange}
        errors={newErrors}
      />
    </ConfigContext.Provider>
  );

  // Errors should be visible because errors.length changed (1 -> 2), triggering useEffect
  expect(screen.getByText('new error')).toBeInTheDocument();
  expect(screen.getByText('global err')).toBeInTheDocument();
});

test('Global errors (empty propertyPath) are hidden immediately after removing an operation', () => {
  const operations = [
    {value: '12', operator: 'div'},
    {value: '25', operator: 'mul'},
  ];
  const onOperationsChange = jest.fn();

  const errors = [{propertyPath: '', message: 'global error', messageTemplate: 'global error', parameters: {}}];

  renderWithConfig(
    <OperationCollection operations={operations} onOperationsChange={onOperationsChange} errors={errors} />
  );

  // Global error should be visible initially
  expect(screen.getByText('global error')).toBeInTheDocument();

  // Remove an operation - triggers setShouldHideErrors(true) synchronously
  // The component re-renders with shouldHideErrors=true, hiding all errors
  fireEvent.click(screen.getAllByTitle('pim_common.remove')[0]);

  // Global error should be hidden immediately because setShouldHideErrors(true) was called
  expect(screen.queryByText('global error')).not.toBeInTheDocument();
});

// ─── Error rendering ───────────────────────────────────────────────────────────

test('It renders global operation errors', () => {
  const operations = [
    {value: '12', operator: 'div'},
    {value: '25', operator: 'mul'},
  ];

  renderWithConfig(
    <OperationCollection
      operations={operations}
      onOperationsChange={() => {}}
      errors={[
        {propertyPath: '', message: 'global error message', messageTemplate: 'global error message', parameters: {}},
      ]}
    />
  );

  expect(screen.getByText('global error message')).toBeInTheDocument();
});

test('It renders indexed operation errors on the correct operation', () => {
  const operations = [
    {value: '12', operator: 'div'},
    {value: '', operator: 'mul'},
  ];

  renderWithConfig(
    <OperationCollection
      operations={operations}
      onOperationsChange={() => {}}
      errors={[{propertyPath: '[1]', message: 'value required', messageTemplate: 'value required', parameters: {}}]}
    />
  );

  expect(screen.getByText('value required')).toBeInTheDocument();
});

test('It does not show errors when errors array is empty', () => {
  renderWithConfig(
    <OperationCollection operations={[{value: '12', operator: 'div'}]} onOperationsChange={() => {}} errors={[]} />
  );

  expect(screen.queryByText('value required')).not.toBeInTheDocument();
});

test('Input shows invalid state when there are errors for that operation', () => {
  renderWithConfig(
    <OperationCollection
      operations={[{value: '', operator: 'mul'}]}
      onOperationsChange={() => {}}
      errors={[
        {
          propertyPath: '[0]',
          message: 'This value should not be blank',
          messageTemplate: 'This value should not be blank',
          parameters: {},
        },
      ]}
    />
  );

  expect(screen.getByText('This value should not be blank')).toBeInTheDocument();
});

// ─── readOnly mode ─────────────────────────────────────────────────────────────

test('In readOnly mode, the input is read-only', () => {
  renderWithConfig(
    <OperationCollection operations={[{value: '12', operator: 'div'}]} onOperationsChange={() => {}} readOnly={true} />
  );

  const input = screen.getByPlaceholderText('measurements.unit.operation.placeholder') as HTMLInputElement;
  expect(input).toHaveAttribute('readonly');
});

test('In readOnly mode, clicking the operator does not open the selector', () => {
  renderWithConfig(
    <OperationCollection operations={[{value: '12', operator: 'div'}]} onOperationsChange={() => {}} readOnly={true} />
  );

  fireEvent.click(screen.getByText('measurements.unit.operator.div'));
  expect(screen.queryByText('measurements.unit.operator.select')).not.toBeInTheDocument();
});

test('In readOnly mode, the add button is not shown', () => {
  renderWithConfig(
    <OperationCollection operations={[{value: '12', operator: 'div'}]} onOperationsChange={() => {}} readOnly={true} />
  );

  expect(screen.queryByText('measurements.unit.operation.add')).not.toBeInTheDocument();
});

test('In readOnly mode, the remove button is not shown even with multiple operations', () => {
  renderWithConfig(
    <OperationCollection
      operations={[
        {value: '12', operator: 'div'},
        {value: '25', operator: 'add'},
      ]}
      onOperationsChange={() => {}}
      readOnly={true}
    />
  );

  expect(screen.queryByTitle('pim_common.remove')).not.toBeInTheDocument();
});

// ─── Default props ─────────────────────────────────────────────────────────────

test('Default errors prop is an empty array', () => {
  renderWithConfig(<OperationCollection operations={[{value: '12', operator: 'div'}]} onOperationsChange={() => {}} />);

  expect(screen.getByPlaceholderText('measurements.unit.operation.placeholder')).toBeInTheDocument();
});

test('Default readOnly prop is false (add button and selector work)', () => {
  renderWithConfig(<OperationCollection operations={[{value: '12', operator: 'div'}]} onOperationsChange={() => {}} />);

  expect(screen.getByText('measurements.unit.operation.add')).toBeInTheDocument();
  fireEvent.click(screen.getByText('measurements.unit.operator.div'));
  expect(screen.getByText('measurements.unit.operator.select')).toBeInTheDocument();
});
