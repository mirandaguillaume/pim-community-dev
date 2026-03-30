<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Infrastructure\Service;

use Akeneo\Connectivity\Connection\Infrastructure\Service\Encrypter;
use PHPUnit\Framework\TestCase;

class EncrypterTest extends TestCase
{
    private Encrypter $sut;

    protected function setUp(): void
    {
    }

    public function test_it_encrypts_a_key(): void
    {
        $this->sut = new Encrypter('AES-256-OFB', 'key', 'key');
        $this->assertSame('q5r5', $this->sut->encrypt('666'));
    }

    public function test_it_decrypts_a_key(): void
    {
        $this->sut = new Encrypter('AES-256-OFB', 'key', 'key');
        $this->assertSame('666', $this->sut->decrypt('q5r5'));
    }

    public function test_it_encrypt_with_an_initializatuon_vector_length_inferior_to_sixteen(): void
    {
        $this->sut = new Encrypter('AES-256-OFB', 'key', 'key');
        $this->assertSame('666', $this->sut->decrypt('q5r5'));
    }

    public function test_it_encrypt_with_an_initializatuon_vector_length_truncated_to_sixteen_characters(): void
    {
        $this->sut = new Encrypter('AES-256-OFB', 'key', '0000000000000key1');
        $this->assertSame('666', $this->sut->decrypt('q5r5'));
    }
}
