<?php

declare(strict_types=1);

namespace Specification\Akeneo\Category\Api\Command\UserIntents;

use Akeneo\Category\Api\Command\UserIntents\SetLabel;
use Akeneo\Category\Api\Command\UserIntents\UserIntent;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SetLabelSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith('en_US', 'The label');
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(SetLabel::class);
        $this->shouldImplement(UserIntent::class);
    }

    public function it_returns_the_locale_code()
    {
        $this->localeCode()->shouldReturn('en_US');
    }

    public function it_returns_the_label_value()
    {
        $this->label()->shouldReturn('The label');
    }

    public function it_can_set_label_null()
    {
        $this->beConstructedWith('en_US', null);
        $this->label()->shouldReturn(null);
    }

    public function it_set_label_to_null_when_empty()
    {
        $this->beConstructedWith('en_US', '');
        $this->label()->shouldReturn(null);
    }
}
