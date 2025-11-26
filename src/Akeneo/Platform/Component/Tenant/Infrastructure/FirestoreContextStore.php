<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2023 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\Component\Tenant\Infrastructure;

use Akeneo\Platform\Component\Tenant\Domain\ContextStoreInterface;
use Akeneo\Platform\Component\Tenant\Domain\Exception\TenantContextNotFoundException;
use Webmozart\Assert\Assert;

/**
 * Context store using Google Firestore
 *
 * @author  JM Leroux <jmleroux.pro@gmail.com>
 */
final class FirestoreContextStore implements ContextStoreInterface
{
    private const MAX_RETRY = 5;

    /**
     * @param object $firestoreClient Expected to be Google\Cloud\Firestore\FirestoreClient when available.
     */
    public function __construct(
        private readonly object $firestoreClient,
        private readonly string $collection,
    ) {
        Assert::notEmpty($collection, 'The collection name must not be empty');
    }

    public function findDocumentById(string $documentId): array
    {
        $retry = 0;
        do {
            $snapshot = $this->findDocumentSnapshot($documentId);
            if (!$snapshot->exists()) {
                $retry++;
                usleep(100000 * $retry);
            }
        } while (!$snapshot->exists() && ($retry < self::MAX_RETRY));

        if (!$snapshot->exists()) {
            throw new TenantContextNotFoundException(
                sprintf('Unable to fetch context for the "%s" tenant ID: the document does not exist', $documentId)
            );
        }

        return $snapshot->data();
    }

    /**
     * @return object DocumentSnapshot-like object
     */
    private function findDocumentSnapshot(string $documentId): object
    {
        $docRef = $this->firestoreClient->collection($this->collection)->document($documentId);

        return $docRef->snapshot();
    }
}
