<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Connectivity\Connection\Domain\WrongCredentialsConnection\Model\Read;

use Akeneo\Connectivity\Connection\Domain\WrongCredentialsConnection\Model\Read\WrongCredentialsCombinations;
use PHPUnit\Framework\TestCase;

class WrongCredentialsCombinationsTest extends TestCase
{
    private WrongCredentialsCombinations $sut;

    protected function setUp(): void
    {
    }

    public function test_it_is_a_wrong_credentials_combinations(): void
    {
        $this->sut = new WrongCredentialsCombinations([]);
        $this->assertTrue(is_a(WrongCredentialsCombinations::class, WrongCredentialsCombinations::class, true));
    }

    public function test_it_normalizes_an_empty_collection(): void
    {
        $this->sut = new WrongCredentialsCombinations([]);
        $this->assertSame([], $this->sut->normalize());
    }

    public function test_it_normalizes_a_collection_of_combination(): void
    {
        $this->sut = new WrongCredentialsCombinations([
                    [
                        'connection_code' => 'bynder',
                        'users' => [
                            'magento' => '2019-05-15T16:25:00+00:00',
                            'erp' => '2019-10-15T16:25:00+00:00',
                        ],
                    ],
                    [
                        'connection_code' => 'magento',
                        'users' => [
                            'bynder' => '2020-05-15T16:25:00+00:00',
                            'erp' => '2020-10-15T16:25:00+00:00',
                        ],
                    ],
                ]);
        $this->assertSame([
                    'bynder' => [
                        'code' => 'bynder',
                        'users' => [
                            ['username' => 'magento', 'date' => '2019-05-15T16:25:00+00:00'],
                            ['username' => 'erp', 'date' => '2019-10-15T16:25:00+00:00'],
                        ],
                    ],
                    'magento' => [
                        'code' => 'magento',
                        'users' => [
                            ['username' => 'bynder', 'date' => '2020-05-15T16:25:00+00:00'],
                            ['username' => 'erp', 'date' => '2020-10-15T16:25:00+00:00'],
                        ],
                    ],
                ], $this->sut->normalize());
    }
}
