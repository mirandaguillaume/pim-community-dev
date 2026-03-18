<?php

namespace spec\Akeneo\Tool\Bundle\StorageUtilsBundle\EventSubscriber;

use Doctrine\Persistence\Event\LoadClassMetadataEventArgs;
use Doctrine\Persistence\Mapping\ClassMetadata;
use PhpSpec\ObjectBehavior;

class ResolveTargetRepositorySubscriberSpec extends ObjectBehavior
{
    public function it_adds_new_targeted_repository(LoadClassMetadataEventArgs $args, ClassMetadata $cm)
    {
        $this->addResolveTargetRepository('foo', 'barRepository');

        $args->getClassMetadata()->willReturn($cm);
        $cm->getName()->willReturn('foo');

        $this->loadClassMetadata($args);
    }
}
