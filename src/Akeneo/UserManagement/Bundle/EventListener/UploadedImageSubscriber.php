<?php

namespace Akeneo\UserManagement\Bundle\EventListener;

use Akeneo\UserManagement\Component\EntityUploadedImageInterface;
use Doctrine\Common\EventSubscriber;
use Doctrine\Persistence\Event\LifecycleEventArgs;

class UploadedImageSubscriber implements EventSubscriber
{
    /**
     * @var string
     */
    protected $webRoot;

    /**
     * Define web root path.
     *
     * @param  string                    $kernelRootDir
     * @throws \InvalidArgumentException
     */
    public function __construct($kernelRootDir)
    {
        $this->webRoot = realpath($kernelRootDir . '/../public');
        if (!$this->webRoot) {
            throw new \InvalidArgumentException('Invalid kernel root');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return [
            'preUpdate',
            'prePersist',
            'postPersist',
            'postUpdate',
            'postRemove',
        ];
    }

    /**
     * Remove uploaded image if any.
     */
    public function postRemove(LifecycleEventArgs $args)
    {
        /** @var EntityUploadedImageInterface $entity */
        $entity = $args->getObject();
        $this->removeImage($entity);
    }

    /**
     * Handle preUpdate.
     */
    public function preUpdate(LifecycleEventArgs $args)
    {
        /** @var EntityUploadedImageInterface $entity */
        $entity = $args->getObject();
        if ($this->hasUploadedImage($entity)) {
            $this->removeImage($entity);

            $this->updateImageName($args);

            $em = $args->getObjectManager();
            $uow = $em->getUnitOfWork();
            $uow->recomputeSingleEntityChangeSet(
                $em->getClassMetadata($entity::class),
                $entity
            );
        }
    }

    /**
     * Handle prePersist.
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $this->updateImageName($args);
    }

    /**
     * Handle postPersist.
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $this->handleImageUpload($args);
    }

    /**
     * Handle postUpdate.
     */
    public function postUpdate(LifecycleEventArgs $args)
    {
        $this->handleImageUpload($args);
    }

    /**
     * Move uploaded image to upload dir.
     */
    protected function handleImageUpload(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();
        if ($this->isExpectedEntity($entity)) {
            if (!$this->hasUploadedImage($entity)) {
                return;
            }

            $dir = $this->getUploadRootDir($entity);
            if (!file_exists($dir)) {
                mkdir($dir, 0o755, true);
            }

            $entity->getImageFile()->move($dir, $entity->getImage());
            $entity->unsetImageFile();
        }
    }

    /**
     * Update uploaded image name.
     */
    protected function updateImageName(LifecycleEventArgs $args)
    {
        /** @var EntityUploadedImageInterface $entity */
        $entity = $args->getObject();
        if ($this->hasUploadedImage($entity)) {
            $filename = sha1(uniqid(random_int(0, mt_getrandmax()), true));
            $entity->setImage($filename . '.' . $entity->getImageFile()->guessExtension());
        }
    }

    /**
     * Get upload directory location in FS.
     *
     * @return string
     */
    protected function getUploadRootDir(EntityUploadedImageInterface $entity)
    {
        return rtrim($this->webRoot, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $entity->getUploadDir();
    }

    /**
     * Remove image.
     *
     * @param EntityUploadedImageInterface $entity
     */
    protected function removeImage($entity)
    {
        if ($this->isExpectedEntity($entity) && $entity->getImage()) {
            $file = $this->getUploadRootDir($entity) . DIRECTORY_SEPARATOR . $entity->getImage();
            if (is_file($file) && is_writable($file)) {
                unlink($file);
            }
        }
    }

    /**
     * Check for new image upload.
     *
     * @param  EntityUploadedImageInterface $entity
     * @return bool
     */
    protected function hasUploadedImage($entity)
    {
        return $this->isExpectedEntity($entity) && null !== $entity->getImageFile();
    }

    /**
     * Check if entity acceptable by subscriber.
     *
     * @param  object $entity
     * @return bool
     */
    protected function isExpectedEntity($entity)
    {
        return $entity instanceof EntityUploadedImageInterface;
    }
}
