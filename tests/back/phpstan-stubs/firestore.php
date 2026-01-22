<?php

declare(strict_types=1);

namespace Google\Cloud\Firestore;

class FirestoreClient
{
    public function collection(string $name): CollectionReference
    {
        return new CollectionReference();
    }
}

class CollectionReference
{
    public function document(string $id): DocumentReference
    {
        return new DocumentReference();
    }
}

class DocumentReference
{
    public function snapshot(): DocumentSnapshot
    {
        return new DocumentSnapshot();
    }
}

class DocumentSnapshot
{
    public function exists(): bool
    {
        return false;
    }

    /**
     * @return array<string, mixed>
     */
    public function data(): array
    {
        return [];
    }
}
