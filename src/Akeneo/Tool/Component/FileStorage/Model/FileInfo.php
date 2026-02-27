<?php

namespace Akeneo\Tool\Component\FileStorage\Model;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;

/**
 * File.
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
#[ORM\Entity(repositoryClass: \Akeneo\Tool\Bundle\FileStorageBundle\Doctrine\ORM\Repository\FileInfoRepository::class)]
#[ORM\Table(name: 'akeneo_file_storage_file_info')]
#[ORM\Index(columns: ['original_filename', 'hash', 'storage'], name: 'original_filename_hash_storage_idx')]
class FileInfo implements FileInfoInterface, \Stringable
{
    /** @var int */
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: Types::INTEGER)]
    protected $id;

    /** @var string */
    #[ORM\Column(name: 'file_key', type: Types::STRING, length: 255, unique: true)]
    protected $key;

    /** @var string */
    #[ORM\Column(name: 'original_filename', type: Types::STRING)]
    protected $originalFilename;

    /** @var string */
    #[ORM\Column(name: 'mime_type', type: Types::STRING, length: 255)]
    protected $mimeType;

    /** @var int */
    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    protected $size;

    /** @var string */
    #[ORM\Column(type: Types::STRING, length: 10)]
    protected $extension;

    /** @var string */
    #[ORM\Column(type: Types::STRING, length: 100, nullable: true)]
    protected $hash;

    /** @var string */
    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    protected $storage;

    /** @var bool */
    protected $removed = false;

    /** @var UploadedFile */
    protected $uploadedFile;

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getOriginalFilename()
    {
        return $this->originalFilename;
    }

    /**
     * {@inheritdoc}
     */
    public function setOriginalFilename($originalFilename)
    {
        $this->originalFilename = $originalFilename;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getMimeType()
    {
        return $this->mimeType;
    }

    /**
     * {@inheritdoc}
     */
    public function setMimeType($mimeType)
    {
        $this->mimeType = $mimeType;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * {@inheritdoc}
     */
    public function setSize($size)
    {
        $this->size = $size;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getExtension()
    {
        return $this->extension;
    }

    /**
     * {@inheritdoc}
     */
    public function setExtension($extension)
    {
        $this->extension = $extension;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * {@inheritdoc}
     */
    public function setHash($hash)
    {
        $this->hash = $hash;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getStorage()
    {
        return $this->storage;
    }

    /**
     * {@inheritdoc}
     */
    public function setStorage($storage)
    {
        $this->storage = $storage;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * {@inheritdoc}
     */
    public function setKey($key)
    {
        $this->key = $key;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getUploadedFile()
    {
        return $this->uploadedFile;
    }

    /**
     * {@inheritdoc}
     */
    public function setUploadedFile(UploadedFile $uploadedFile = null)
    {
        $this->uploadedFile = $uploadedFile;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setRemoved($removed)
    {
        $this->removed = $removed;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isRemoved()
    {
        return $this->removed;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->getOriginalFilename();
    }
}
