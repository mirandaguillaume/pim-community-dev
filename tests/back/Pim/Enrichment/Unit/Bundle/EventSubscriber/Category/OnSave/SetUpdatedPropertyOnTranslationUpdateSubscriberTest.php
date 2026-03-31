<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Bundle\EventSubscriber\Category\OnSave;

use Akeneo\Category\Infrastructure\Component\Model\CategoryInterface;
use Akeneo\Category\Infrastructure\Component\Model\CategoryTranslation;
use Akeneo\Pim\Enrichment\Bundle\EventSubscriber\Category\OnSave\SetUpdatedPropertyOnTranslationUpdateSubscriber;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SetUpdatedPropertyOnTranslationUpdateSubscriberTest extends TestCase
{
    private SetUpdatedPropertyOnTranslationUpdateSubscriber $sut;

    protected function setUp(): void
    {
        $this->sut = new SetUpdatedPropertyOnTranslationUpdateSubscriber();
    }

    public function test_it_only_handles_category_translation(): void
    {
        $this->expectNotToPerformAssertions();
        $objectManager = $this->createMock(ObjectManager::class);

        $this->sut->preUpdate(new LifecycleEventArgs(new \stdClass(), $objectManager));
    }

    public function test_it_sets_the_updated_property_on_a_translation_update(): void
    {
        $objectManager = $this->createMock(ObjectManager::class);
        $category = $this->createMock(CategoryInterface::class);

        $translation = new CategoryTranslation();
        $translation->setForeignKey($category);
        $category->expects($this->once())->method('setUpdated')->with($this->anything())->willReturn($category);
        $this->sut->preUpdate(new LifecycleEventArgs($translation, $objectManager));
    }

    public function test_it_sets_the_updated_property_on_a_translation_persist(): void
    {
        $objectManager = $this->createMock(ObjectManager::class);
        $category = $this->createMock(CategoryInterface::class);

        $translation = new CategoryTranslation();
        $translation->setForeignKey($category);
        $category->expects($this->once())->method('setUpdated')->with($this->anything())->willReturn($category);
        $this->sut->prePersist(new LifecycleEventArgs($translation, $objectManager));
    }

    public function test_it_sets_the_updated_property_on_a_translation_remove(): void
    {
        $objectManager = $this->createMock(ObjectManager::class);
        $category = $this->createMock(CategoryInterface::class);

        $translation = new CategoryTranslation();
        $translation->setForeignKey($category);
        $category->expects($this->once())->method('setUpdated')->with($this->anything())->willReturn($category);
        $this->sut->preRemove(new LifecycleEventArgs($translation, $objectManager));
    }
}
