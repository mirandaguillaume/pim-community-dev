<?php

namespace spec\Akeneo\Tool\Component\Batch\Model;

use Akeneo\Tool\Component\Batch\Model\StepExecution;
use PhpSpec\ObjectBehavior;

class WarningSpec extends ObjectBehavior
{
    public function let(StepExecution $stepExecution)
    {
        $this->beConstructedWith(
            $stepExecution,
            'my reason',
            ['myparam' => 'mavalue'],
            ['myitem' => 'myvalue']
        );
    }

    public function it_provides_a_step_execution($stepExecution)
    {
        $this->getStepExecution()->shouldReturn($stepExecution);
    }

    public function it_provides_array_format()
    {
        $this->toArray()->shouldReturn(
            [
                'reason' => 'my reason',
                'reasonParameters' => [
                    'myparam' => 'mavalue',
                ],
                'item' => [
                    'myitem' => 'myvalue',
                ],
            ]
        );
    }
}
