<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\Api\Normalizer\Exception;

use Akeneo\Tool\Component\Api\Exception\DocumentedHttpException;
use PHPUnit\Framework\TestCase;
use spec\Akeneo\Tool\Component\Api\Normalizer\Exception\DocumentedNormalizer;
use Symfony\Component\HttpFoundation\Response;

class DocumentedNormalizerTest extends TestCase
{
    private DocumentedNormalizer $sut;

    protected function setUp(): void
    {
        $this->sut = new DocumentedNormalizer();
    }

    public function test_it_normalizes_an_exception(): void
    {
        $exception = new DocumentedHttpException(
            'http://example.net',
            'Property "xx" does not exist'
        );
        $this->assertSame([
                    'code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                    'message' => 'Property "xx" does not exist',
                    '_links' => [
                        'documentation' => [
                            'href' => 'http://example.net',
                        ],
                    ],
                ], $this->sut->normalize($exception));
    }
}
