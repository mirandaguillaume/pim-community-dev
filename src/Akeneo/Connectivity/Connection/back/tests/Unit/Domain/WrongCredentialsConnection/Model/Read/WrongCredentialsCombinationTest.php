<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Domain\WrongCredentialsConnection\Model\Read;

use Akeneo\Connectivity\Connection\Domain\WrongCredentialsConnection\Model\Read\WrongCredentialsCombination;
use PHPUnit\Framework\TestCase;

class WrongCredentialsCombinationTest extends TestCase
{
    private WrongCredentialsCombination $sut;

    protected function setUp(): void
    {
        $this->sut = new WrongCredentialsCombination('magento');
    }

    public function test_it_is_a_wrong_credentials_combination(): void
    {
        $this->assertInstanceOf(WrongCredentialsCombination::class, $this->sut);
    }

    public function test_it_provides_empty_array_if_there_is_no_users(): void
    {
        $this->assertSame([], $this->sut->users());
    }

    public function test_it_adds_and_provides_users(): void
    {
        $firstDate = new \DateTime('2019-05-15T16:25:00+00:00');
        $this->sut->addUser('bynder', $firstDate);
        $this->assertSame(['bynder' => $firstDate], $this->sut->users());
        $secondDate = new \DateTime();
        $this->sut->addUser('dadada', $secondDate);
        $this->assertSame([
                    'bynder' => $firstDate,
                    'dadada' => $secondDate,
                ], $this->sut->users());
    }

    public function test_it_adds_and_provides_users_without_duplicating_them(): void
    {
        $firstDate = new \DateTime('2019-05-15T16:25:00+00:00');
        $secondDate = new \DateTime('2020-02-14T12:03:40+00:00');
        $this->sut->addUser('bynder', $firstDate);
        $this->assertSame(['bynder' => $firstDate], $this->sut->users());
        $this->sut->addUser('bynder', $secondDate);
        $this->assertSame(['bynder' => $secondDate], $this->sut->users());
    }

    public function test_it_provides_a_connection_code(): void
    {
        $this->assertSame('magento', $this->sut->connectionCode());
    }

    public function test_it_normalizes_an_empty_object(): void
    {
        $this->assertSame([
                    'code' => 'magento',
                    'users' => [],
                ], $this->sut->normalize());
    }

    public function test_it_normalizes(): void
    {
        $bynderDate = new \DateTime('2019-05-15T16:25:00+00:00');
        $anotherDate = new \DateTime('2020-02-14T12:03:40+00:00');
        $this->sut->addUser('bynder', $bynderDate);
        $this->sut->addUser('dadada', $anotherDate);
        $this->assertSame([
                    'code' => 'magento',
                    'users' => [
                        [
                            'username' => 'bynder',
                            'date' => '2019-05-15T16:25:00+00:00',
                        ],
                        [
                            'username' => 'dadada',
                            'date' => '2020-02-14T12:03:40+00:00',
                        ],
                    ],
                ], $this->sut->normalize());
    }
}
