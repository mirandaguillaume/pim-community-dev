import React from 'react';
import {ProgressIndicator} from './ProgressIndicator';
import {Button} from '../Button/Button';
import {useProgress} from '../../hooks';

export default {
  title: 'Components/Progress indicator',
  component: ProgressIndicator,

  subcomponents: {
    'ProgressIndicator.Step': ProgressIndicator.Step,
  },

  argTypes: {
    current: {
      name: '<ProgressIndicator current>',
      description: 'Define the current step of the progress',
    },
  },
};

export const Standard = {
  render: args => {
    const steps = ['choose', 'edit', 'confirm'];
    const [isCurrent, next, previous] = useProgress(steps);

    return (
      <>
        <div
          style={{
            display: 'flex',
            justifyContent: 'space-evenly',
          }}
        >
          <Button level="secondary" onClick={previous}>
            Previous
          </Button>
          <Button level="secondary" onClick={next}>
            Next
          </Button>
        </div>
        <ProgressIndicator>
          {steps.map(step => {
            return (
              <ProgressIndicator.Step key={step} current={isCurrent(step)}>
                {step}
              </ProgressIndicator.Step>
            );
          })}
        </ProgressIndicator>
      </>
    );
  },

  name: 'Standard',

  parameters: {
    docs: {
      source: {
        code: `import {useProgress, ProgressIndicator} from 'akeneo-design-system';\n
//Step codes needs to be unique
const steps = ['choose', 'edit', 'confirm'];
const [isCurrent, next, previous] = useProgress(steps);
return (
  <>
    <Button onClick={previous}>Previous</Button>
    <Button onClick={next}>Next</Button>
    <ProgressIndicator>
      {steps.map((step) => (
        <ProgressIndicator.Step key={step} current={isCurrent(step)}>
          {/* Here we used the step code, but you can also use a translator like {translate(\`my_translation_path.$\{step}\`)}*/}
          {step}
        </ProgressIndicator.Step>
      ))}
    </ProgressIndicator>
  </>
);
`,
      },
    },
  },
};

export const States = {
  render: () => (
    <ProgressIndicator>
      <ProgressIndicator.Step>Before current step</ProgressIndicator.Step>
      <ProgressIndicator.Step current={true}>Current step</ProgressIndicator.Step>
      <ProgressIndicator.Step>After current step</ProgressIndicator.Step>
    </ProgressIndicator>
  ),

  name: 'States',
};

export const Disabled = {
  render: () => (
    <ProgressIndicator>
      <ProgressIndicator.Step disabled={true}>Disabled step</ProgressIndicator.Step>
      <ProgressIndicator.Step disabled={false} current={true}>
        Current step enabled
      </ProgressIndicator.Step>
      <ProgressIndicator.Step disabled={true}>Next disabled step</ProgressIndicator.Step>
    </ProgressIndicator>
  ),

  name: 'Disabled',
};
