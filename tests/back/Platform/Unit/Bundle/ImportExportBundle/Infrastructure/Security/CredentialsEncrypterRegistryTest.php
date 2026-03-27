<?php

declare(strict_types=1);

namespace Akeneo\Test\Platform\Unit\Bundle\ImportExportBundle\Infrastructure\Security;

use Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\Security\CredentialsEncrypter;
use Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\Security\CredentialsEncrypterRegistry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CredentialsEncrypterRegistryTest extends TestCase
{
    private const PREVIOUS_DATA = [
        'configuration' => [
            'storage' => [
                'type' => 'sftp',
                'username' => 'username',
                'host' => 'host',
                'port' => '22',
                'password' => 'another_secret',
            ],
        ],
    ];

    private const CLEAR_DATA = [
        'configuration' => [
            'storage' => [
                'type' => 'sftp',
                'username' => 'username',
                'host' => 'host',
                'port' => '22',
                'password' => 's3cr3t',
            ],
        ],
    ];

    private const ENCRYPTED_DATA = [
        'configuration' => [
            'storage' => [
                'type' => 'sftp',
                'username' => 'username',
                'host' => 'host',
                'port' => '22',
                'password' => 'encrypted_password',
            ],
        ],
    ];

    private const OBFUSCATED_DATA = [
        'configuration' => [
            'storage' => [
                'type' => 'sftp',
                'username' => 'username',
                'host' => 'host',
                'port' => '22',
            ],
        ],
    ];

    private CredentialsEncrypter|MockObject $encrypter;

    protected function setUp(): void
    {
        $this->encrypter = $this->createMock(CredentialsEncrypter::class);
        $this->encrypter->method('support')->willReturnCallback(function (array $data): bool {
            return $data === self::CLEAR_DATA || $data === self::ENCRYPTED_DATA;
        });
        $this->encrypter->method('encryptCredentials')->with(self::PREVIOUS_DATA, self::CLEAR_DATA)->willReturn(self::ENCRYPTED_DATA);
        $this->encrypter->method('obfuscateCredentials')->with(self::CLEAR_DATA)->willReturn(self::OBFUSCATED_DATA);
    }

    public function test_it_encrypts_credentials(): void
    {
        $sut = new CredentialsEncrypterRegistry([$this->encrypter]);
        $this->assertSame(self::ENCRYPTED_DATA, $sut->encryptCredentials(self::PREVIOUS_DATA, self::CLEAR_DATA));
    }

    public function test_it_obfuscates_credentials(): void
    {
        $sut = new CredentialsEncrypterRegistry([$this->encrypter]);
        $this->assertSame(self::OBFUSCATED_DATA, $sut->obfuscateCredentials(self::CLEAR_DATA));
    }

    public function test_it_returns_data_as_it_is_if_no_encrypter_supports_it(): void
    {
        $sut = new CredentialsEncrypterRegistry([]);
        $this->assertSame(self::CLEAR_DATA, $sut->encryptCredentials(self::PREVIOUS_DATA, self::CLEAR_DATA));
        $this->assertSame(self::CLEAR_DATA, $sut->obfuscateCredentials(self::CLEAR_DATA));
    }
}
