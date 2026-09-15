<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Structure\Integration\Doctrine\ORM\Repository;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

/**
 * AttributeRepository::getAttributesByGroups is the query MoveChildAttributesTasklet pages through, with a search-after
 * on the attribute code, to move the attributes of the attribute groups being bulk deleted. Its unit tests mock it.
 *
 * @copyright 2026 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeRepositoryGetAttributesByGroupsIntegration extends TestCase
{
    private AttributeRepositoryInterface $attributeRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->attributeRepository = $this->get('pim_catalog.repository.attribute');

        $this->createAttributeGroups(['group_one', 'group_two', 'group_three']);
        // Created out of code order, so the ordering has to come from the query.
        $this->createTextAttribute('delta', 'group_two');
        $this->createTextAttribute('alpha', 'group_one');
        $this->createTextAttribute('echo', 'group_three');
        $this->createTextAttribute('charlie', 'group_one');
        $this->createTextAttribute('bravo', 'group_two');
    }

    public function test_it_returns_only_the_attributes_of_the_given_groups_ordered_by_code(): void
    {
        $attributes = $this->attributeRepository->getAttributesByGroups(['group_one', 'group_two'], 100, null);

        Assert::assertContainsOnlyInstancesOf(AttributeInterface::class, $attributes);
        Assert::assertSame(['alpha', 'bravo', 'charlie', 'delta'], $this->codes($attributes));
        foreach ($attributes as $attribute) {
            Assert::assertContains($attribute->getGroup()->getCode(), ['group_one', 'group_two']);
        }
    }

    public function test_it_respects_the_limit_and_returns_the_next_page_after_the_given_code(): void
    {
        $groups = ['group_one', 'group_two'];

        Assert::assertSame(
            ['alpha', 'bravo', 'charlie'],
            $this->codes($this->attributeRepository->getAttributesByGroups($groups, 3, null)),
        );
        Assert::assertSame(
            ['delta'],
            $this->codes($this->attributeRepository->getAttributesByGroups($groups, 3, 'charlie')),
        );
        Assert::assertSame([], $this->attributeRepository->getAttributesByGroups($groups, 3, 'delta'));
    }

    public function test_paging_the_way_the_move_child_attributes_tasklet_does_reaches_every_attribute_once(): void
    {
        $pages = [];
        $searchAfter = null;
        do {
            $page = $this->attributeRepository->getAttributesByGroups(['group_one', 'group_two', 'group_three'], 2, $searchAfter);
            $pages[] = $this->codes($page);
            $searchAfter = [] === $page ? null : end($page)->getCode();
        } while ([] !== $page && \count($pages) < 10);

        Assert::assertSame([['alpha', 'bravo'], ['charlie', 'delta'], ['echo'], []], $pages);
    }

    public function test_it_returns_nothing_for_groups_without_attributes_or_unknown_groups(): void
    {
        $this->createAttributeGroups(['empty_group']);

        Assert::assertSame([], $this->attributeRepository->getAttributesByGroups(['empty_group'], 100, null));
        Assert::assertSame([], $this->attributeRepository->getAttributesByGroups(['unknown_group'], 100, null));
    }

    /**
     * @param AttributeInterface[] $attributes
     *
     * @return string[]
     */
    private function codes(array $attributes): array
    {
        return array_map(static fn (AttributeInterface $attribute): string => $attribute->getCode(), $attributes);
    }

    /**
     * @param string[] $codes
     */
    private function createAttributeGroups(array $codes): void
    {
        foreach ($codes as $code) {
            $attributeGroup = $this->get('pim_catalog.factory.attribute_group')->create();
            $this->get('pim_catalog.updater.attribute_group')->update($attributeGroup, ['code' => $code]);
            $violations = $this->get('validator')->validate($attributeGroup);
            Assert::assertCount(0, $violations, (string) $violations);
            $this->get('pim_catalog.saver.attribute_group')->save($attributeGroup);
        }
    }

    private function createTextAttribute(string $code, string $groupCode): void
    {
        $attribute = $this->get('pim_catalog.factory.attribute')->create();
        $this->get('pim_catalog.updater.attribute')->update(
            $attribute,
            ['code' => $code, 'type' => 'pim_catalog_text', 'group' => $groupCode],
        );
        $violations = $this->get('validator')->validate($attribute);
        Assert::assertCount(0, $violations, (string) $violations);
        $this->get('pim_catalog.saver.attribute')->save($attribute);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
