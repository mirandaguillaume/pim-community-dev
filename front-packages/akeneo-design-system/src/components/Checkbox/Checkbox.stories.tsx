import type {Meta, StoryObj} from '@storybook/react';
import {useState} from 'react';
import {Checkbox} from './Checkbox.tsx';

const meta: Meta<typeof Checkbox> = {
  title: 'Components/Checkbox',
  component: Checkbox,
  argTypes: {
    readOnly: {control: {type: 'boolean'}},
    checked: {control: {type: 'select'}, options: [true, false, 'mixed']},
    onChange: {action: 'Checkbox component onChange'},
  },
  args: {
    checked: true,
    children: 'Checkbox',
  },
};

export default meta;
type Story = StoryObj<typeof meta>;

export const Standard: Story = {
  name: 'Standard',
  render: (args) => (
    const [{checked}, updateArgs] = useArgs();
      const toggleChecked = () => {
        updateArgs({checked: !checked});
      };
      return <Checkbox {...args} checked={checked} onChange={toggleChecked} />;
  ),
};

export const State: Story = {
  name: 'State',
  render: (args) => (
    <>
          <Checkbox {...args} checked={true}>
            Checkbox checked
          </Checkbox>
          <Checkbox {...args} checked="mixed">
            Checkbox mixed
          </Checkbox>
          <Checkbox {...args} checked={false}>
            Checkbox false
          </Checkbox>
        </>
  ),
};

export const Disabled: Story = {
  name: 'Disabled',
  render: (args) => (
    <>
          <Checkbox {...args} readOnly={true} checked={true}>
            Checked disabled
          </Checkbox>
          <Checkbox {...args} readOnly={true} checked="mixed">
            Mixed disabled
          </Checkbox>
          <Checkbox {...args} readOnly={true} checked={false}>
            Unchecked disabled
          </Checkbox>
        </>
  ),
};

export const Animation: Story = {
  name: 'Animation',
  render: (args) => {
    const [checked, setChecked] = useState(true);
      return <Checkbox {...args} checked={checked} onChange={newChecked => setChecked(newChecked)} />;
  },
};

