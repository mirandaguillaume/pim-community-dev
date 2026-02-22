<?php

namespace Oro\Bundle\SecurityBundle\Metadata;

use Oro\Bundle\SecurityBundle\Annotation\Acl as AclAnnotation;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor as AclAnnotationAncestor;

class AclAnnotationStorage
{
    /**
     * @var AclAnnotation[]
     *   key = annotation id
     *   value = annotation object
     */
    private array $annotations = [];

    /**
     * @var string[]
     *   key = class name
     *   value = array of methods
     *              key = method name ('!' for class if it have an annotation)
     *              value = annotation id bound to the method
     */
    private array $classes = [];

    /**
     * Gets an annotation by its id
     *
     * @param  string                    $id
     * @throws \InvalidArgumentException
     * @return AclAnnotation|null        AclAnnotation object or null if ACL annotation was not found
     */
    public function findById($id): ?AclAnnotation
    {
        if (empty($id)) {
            throw new \InvalidArgumentException('$id must not be empty.');
        }

        return $this->annotations[$id] ?? null;
    }

    /**
     * Gets an annotation bound to the given class/method
     *
     * @param  string                    $class
     * @throws \InvalidArgumentException
     * @return AclAnnotation|null        AclAnnotation object or null if ACL annotation was not found
     */
    public function find($class, ?string $method = null): ?AclAnnotation
    {
        if (empty($class)) {
            throw new \InvalidArgumentException('$class must not be empty.');
        }

        if (empty($method)) {
            if (!isset($this->classes[$class]['!'])) {
                return null;
            }
            $id = $this->classes[$class]['!'];
        } else {
            if (!isset($this->classes[$class][$method])) {
                return null;
            }
            $id = $this->classes[$class][$method];
        }

        return $this->annotations[$id] ?? null;
    }

    /**
     * Determines whether the given class/method has an annotation
     *
     * @param  string      $class
     * @return bool
     */
    public function has($class, ?string $method = null)
    {
        if (empty($method)) {
            if (!isset($this->classes[$class]['!'])) {
                return false;
            }
            $id = $this->classes[$class]['!'];
        } else {
            if (!isset($this->classes[$class][$method])) {
                return false;
            }
            $id = $this->classes[$class][$method];
        }

        return isset($this->annotations[$id]);
    }

    /**
     * Gets annotations
     *
     * @param  string|null     $type The annotation type
     * @return AclAnnotation[]
     */
    public function getAnnotations(?string $type = null)
    {
        if ($type === null) {
            return array_values($this->annotations);
        }

        $result = [];
        foreach ($this->annotations as $annotation) {
            if ($annotation->getType() === $type) {
                $result[] = $annotation;
            }
        }

        return $result;
    }

    /**
     * Checks whether the given class is registered in this storage
     *
     * @param  string $class
     * @return bool   true if the class is registered in this storage; otherwise, false
     */
    public function isKnownClass($class)
    {
        return isset($this->classes[$class]);
    }

    /**
     * Checks whether the given method is registered in this storage
     *
     * @param  string $class
     * @param  string $method
     * @return bool   true if the method is registered in this storage; otherwise, false
     */
    public function isKnownMethod($class, $method)
    {
        return isset($this->classes[$class]) && isset($this->classes[$class][$method]);
    }

    /**
     * Adds an annotation
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function add(AclAnnotation $annotation, ?string $class = null, ?string $method = null)
    {
        $id = $annotation->getId();
        $this->annotations[$id] = $annotation;
        if ($class !== null) {
            $this->addBinding($id, $class, $method);
        }
    }

    /**
     * Adds an annotation ancestor
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function addAncestor(AclAnnotationAncestor $ancestor, ?string $class = null, ?string $method = null)
    {
        if ($class !== null) {
            $this->addBinding($ancestor->getId(), $class, $method);
        }
    }

    /**
     * Adds an annotation binding
     *
     * @param  string                    $id
     * @param  string                    $class
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function addBinding($id, $class, ?string $method = null)
    {
        if (empty($class)) {
            throw new \InvalidArgumentException('$class must not be empty.');
        }

        if (isset($this->classes[$class])) {
            if (empty($method)) {
                if (isset($this->classes[$class]['!']) && $this->classes[$class]['!'] !== $id) {
                    throw new \RuntimeException(
                        sprintf(
                            'Duplicate binding for "%s". New Id: %s. Existing Id: %s',
                            $class,
                            $id,
                            $this->classes[$class]['!']
                        )
                    );
                }
                $this->classes[$class]['!'] = $id;
            } else {
                if (isset($this->classes[$class][$method]) && $this->classes[$class][$method] !== $id) {
                    throw new \RuntimeException(
                        sprintf(
                            'Duplicate binding for "%s". New Id: %s. Existing Id: %s',
                            $class . '::' . $method,
                            $id,
                            $this->classes[$class][$method]
                        )
                    );
                }
                $this->classes[$class][$method] = $id;
            }
        } else {
            if (empty($method)) {
                $this->classes[$class] = ['!' => $id];
            } else {
                $this->classes[$class] = [$method => $id];
            }
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function __serialize(): array
    {
        return [
            'annotations' => $this->annotations,
            'classes' => $this->classes,
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    public function __unserialize(array $data): void
    {
        $this->annotations = $data['annotations'];
        $this->classes = $data['classes'];
    }
}
