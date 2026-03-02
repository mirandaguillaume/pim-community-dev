<?php

namespace spec\Akeneo\Tool\Component\Localization\Presenter;

use Akeneo\Tool\Component\Localization\Presenter\BooleanPresenter;
use PhpSpec\ObjectBehavior;

class BooleanPresenterSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith(['enabled']);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(BooleanPresenter::class);
    }

    public function it_supports_enabled()
    {
        $this->supports('enabled')->shouldReturn(true);
        $this->supports('yolo')->shouldReturn(false);
    }

    public function it_presents_values()
    {
        $this->present(true)->shouldReturn('true');
        $this->present('true')->shouldReturn('true');
        $this->present('1')->shouldReturn('true');
        $this->present(1)->shouldReturn('true');
        $this->present(false)->shouldReturn('false');
        $this->present('false')->shouldReturn('false');
        $this->present('0')->shouldReturn('false');
        $this->present(0)->shouldReturn('false');
        $this->present('yolo')->shouldReturn('');
    }
}
