<?php

declare(strict_types=1);

namespace Akeneo\Test\Platform\Unit\Bundle\UIBundle\Controller;

use Akeneo\Pim\Enrichment\Component\Product\Repository\GroupRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ReferenceDataRepositoryInterface;
use Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Repository\AttributeOptionRepository;
use Akeneo\Pim\Structure\Component\Model\ReferenceDataConfigurationInterface;
use Akeneo\Pim\Structure\Component\ReferenceData\ConfigurationRegistryInterface;
use Akeneo\Platform\Bundle\UIBundle\Controller\AjaxOptionController;
use Akeneo\Tool\Component\StorageUtils\Repository\SearchableRepositoryInterface;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;

class AjaxOptionControllerTest extends TestCase
{
    private ManagerRegistry|MockObject $doctrine;
    private ConfigurationRegistryInterface|MockObject $registry;
    private AjaxOptionController $sut;

    protected function setUp(): void
    {
        $this->doctrine = $this->createMock(ManagerRegistry::class);
        $this->registry = $this->createMock(ConfigurationRegistryInterface::class);
        $this->sut = new AjaxOptionController($this->doctrine, $this->registry);
    }

    private function createRequestMock(array $getMap): Request|MockObject
    {
        $request = $this->createMock(Request::class);
        $request->method('get')->willReturnCallback(function (string $key, $default = null) use ($getMap) {
            return $getMap[$key] ?? $default;
        });

        return $request;
    }

    public function test_it_returns_options_with_option_repository(): void
    {
        $request = $this->createRequestMock([
            'search' => 'hello',
            'referenceDataName' => null,
            'class' => 'Foo\Bar',
            'dataLocale' => 'fr_FR',
            'collectionId' => 42,
            'isCreatable' => false,
        ]);
        $query = $this->createMock(ParameterBag::class);
        $repository = $this->createMock(AttributeOptionRepository::class);

        $this->doctrine->method('getRepository')->with('Foo\Bar')->willReturn($repository);
        $request->query = $query;
        $query->method('all')->willReturn(['options' => []]);
        $repository->expects($this->once())->method('getOptions')->with('fr_FR', 42, 'hello', []);
        $this->sut->listAction($request);
    }

    public function test_it_returns_options_with_reference_data_repository(): void
    {
        $configuration = $this->createMock(ReferenceDataConfigurationInterface::class);
        $request = $this->createRequestMock([
            'search' => 'hello',
            'referenceDataName' => 'color',
            'class' => 'undefined',
            'isCreatable' => false,
        ]);
        $query = $this->createMock(ParameterBag::class);
        $repository = $this->createMock(ReferenceDataRepositoryInterface::class);

        $this->registry->method('get')->with('color')->willReturn($configuration);
        $configuration->method('getClass')->willReturn('Foo\RefData');
        $this->doctrine->method('getRepository')->with('Foo\RefData')->willReturn($repository);
        $request->query = $query;
        $query->method('all')->willReturn(['options' => []]);
        $repository->expects($this->once())->method('findBySearch')->with('hello', []);
        $this->sut->listAction($request);
    }

    public function test_it_returns_options_with_searchable_repository(): void
    {
        $request = $this->createRequestMock([
            'search' => 'hello',
            'referenceDataName' => null,
            'class' => 'Foo\Bar',
            'isCreatable' => false,
        ]);
        $query = $this->createMock(ParameterBag::class);
        $repository = $this->createMock(SearchableRepositoryInterface::class);

        $this->doctrine->method('getRepository')->with('Foo\Bar')->willReturn($repository);
        $request->query = $query;
        $query->method('all')->willReturn(['options' => []]);
        $repository->expects($this->once())->method('findBySearch')->with('hello', []);
        $this->sut->listAction($request);
    }

    public function test_it_returns_options_with_other_repository(): void
    {
        $request = $this->createRequestMock([
            'search' => 'hello',
            'referenceDataName' => null,
            'class' => 'Foo\Bar',
            'dataLocale' => 'fr_FR',
            'collectionId' => 42,
            'isCreatable' => false,
        ]);
        $query = $this->createMock(ParameterBag::class);
        $repository = $this->createMock(GroupRepositoryInterface::class);

        $this->doctrine->method('getRepository')->with('Foo\Bar')->willReturn($repository);
        $request->query = $query;
        $query->method('all')->willReturn(['options' => []]);
        $repository->expects($this->once())->method('getOptions')->with('fr_FR', 42, 'hello', []);
        $this->sut->listAction($request);
    }

    public function test_it_throws_an_exception_if_no_repository_can_be_found(): void
    {
        $request = $this->createRequestMock([
            'search' => 'hello',
            'referenceDataName' => null,
            'class' => 'Foo\Bar',
        ]);
        $repository = new \stdClass();

        $this->doctrine->method('getRepository')->with('Foo\Bar')->willReturn($repository);
        $this->expectException(\LogicException::class);
        $this->sut->listAction($request);
    }
}
