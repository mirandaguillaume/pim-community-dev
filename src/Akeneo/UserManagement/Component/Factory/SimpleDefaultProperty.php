<?php

namespace Akeneo\UserManagement\Component\Factory;

use Akeneo\UserManagement\Component\Model\UserInterface;

/**
 * Mutates simple default property of a user
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SimpleDefaultProperty implements DefaultProperty
{
    public function __construct(private readonly string $propertyName, private readonly mixed $defaultPropertyValue) {}

    /**
     * {@inheritdoc}
     */
    public function mutate(UserInterface $user): UserInterface
    {
        $user->addProperty($this->propertyName, $this->defaultPropertyValue);

        return $user;
    }
}
