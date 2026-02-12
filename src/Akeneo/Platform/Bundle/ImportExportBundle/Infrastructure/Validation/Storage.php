<?php

namespace Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\Validation;

use Symfony\Component\Validator\Constraint;

class Storage extends Constraint
{
    final public const UNAVAILABLE_TYPE = 'pim_import_export.form.job_instance.validation.storage.unavailable_type';

    public function __construct(
        /** @var string[] */
        private readonly array $filePathSupportedFileExtensions,
    ) {
        parent::__construct();
    }

    public function getFilePathSupportedFileExtensions(): array
    {
        return $this->filePathSupportedFileExtensions;
    }
}
