<?php

namespace Oro\Bundle\ConfigBundle\Config;

use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\ConfigBundle\Entity\Config;
use Oro\Bundle\ConfigBundle\Entity\ConfigValue;
use Symfony\Component\Form\FormInterface;

class ConfigManager
{
    final public const SECTION_VIEW_SEPARATOR = '___';
    final public const SECTION_MODEL_SEPARATOR = '.';

    /**
     * @var ObjectManager
     */
    protected $om;

    /**
     * @var array
     */
    protected $storedSettings = [];

    /**
     *
     * @param array         $settings
     */
    public function __construct(ObjectManager $om, protected $settings = [])
    {
        $this->om = $om;
    }

    /**
     * Get setting value
     *
     * @param  string $name Setting name, for example "pim_user.level"
     * @param bool $default
     * @param bool $full
     * @return mixed
     */
    public function get($name, $default = false, $full = false)
    {
        $settings = [];
        $entity = $this->getScopedEntityName();
        $entityId = $this->getScopeId();
        $this->loadStoredSettings($entity, $entityId);

        $name = explode(self::SECTION_MODEL_SEPARATOR, $name);
        $section = $name[0];
        $key = $name[1];

        if ($default) {
            $settings = $this->settings;
        } elseif (isset($this->storedSettings[$entity][$entityId][$section][$key])) {
            $settings = $this->storedSettings[$entity][$entityId];
        } elseif (isset($this->settings[$section][$key])) {
            $settings = $this->settings;
        }

        if (empty($settings[$section][$key])) {
            return null;
        } else {
            $setting = $settings[$section][$key];

            return is_array($setting) && !$full ? $setting['value'] : $setting;
        }
    }

    /**
     * Save settings with fallback to global scope (default)
     * and change storedSettings with the new settings
     */
    public function save($newSettings)
    {
        $entityName = $this->getScopedEntityName();
        $entityId   = $this->getScopeId();

        /** @var Config $config */
        $config = $this->om
            ->getRepository(Config::class)
            ->getByEntity($entityName, $entityId);

        [$updated, $removed] = $this->getChanged($newSettings);

        foreach ($updated as $newItemKey => $newItemValue) {
            $newItemKey = explode(self::SECTION_VIEW_SEPARATOR, (string) $newItemKey);
            $newItemValue = is_array($newItemValue) ? $newItemValue['value'] : $newItemValue;

            /** @var ConfigValue $value */
            $value = $config->getOrCreateValue($newItemKey[0], $newItemKey[1]);
            $value->setValue($newItemValue);

            $config->getValues()->add($value);

            $this->storedSettings[$entityName][$entityId][$newItemKey[0]][$newItemKey[1]] = $newItemValue;
        }

        $this->om->persist($config);
        $this->om->flush();
    }

    /**
     * @param $newSettings
     * @return array
     */
    public function getChanged($newSettings)
    {
        // find new and updated
        $updated = [];
        $removed = [];
        foreach ($newSettings as $key => $value) {
            $currentValue = $this->get(
                str_replace(
                    self::SECTION_VIEW_SEPARATOR,
                    self::SECTION_MODEL_SEPARATOR,
                    (string) $key
                ),
                false,
                true
            );

            // save only if setting exists and there's no default checkbox checked
            if (!is_null($currentValue) && empty($value['use_parent_scope_value'])) {
                $updated[$key] = $value;
            }

            $valueDefined = isset($currentValue['use_parent_scope_value'])
                && $currentValue['use_parent_scope_value'] == false;
            $valueStillDefined = isset($value['use_parent_scope_value'])
                && $value['use_parent_scope_value'] == false;

            if ($valueDefined && !$valueStillDefined) {
                $key = explode(self::SECTION_VIEW_SEPARATOR, (string) $key);
                $removed[] = [$key[0], $key[1]];
            }
        }

        return [$updated, $removed];
    }

    /**
     * @param $entity
     * @param $entityId
     * @param null $section
     * @return bool
     */
    public function loadStoredSettings($entity, $entityId, $section = null)
    {
        if (isset($this->storedSettings[$entity][$entityId])) {
            return false;
        }

        $this->storedSettings[$entity][$entityId] = $this->om
            ->getRepository(Config::class)
            ->loadSettings($entity, $entityId, $section);

        return true;
    }

    /**
     * @return array
     */
    public function getSettingsByForm(FormInterface $form)
    {
        $settings = [];

        foreach ($form as $child) {
            $key = str_replace(
                self::SECTION_VIEW_SEPARATOR,
                self::SECTION_MODEL_SEPARATOR,
                $child->getName()
            );
            $settings[$child->getName()] = $this->get($key, false, true);

            $settings[$child->getName()]['use_parent_scope_value'] =
                !isset($settings[$child->getName()]['use_parent_scope_value']) ?
                    true : $settings[$child->getName()]['use_parent_scope_value'];
        }

        return $settings;
    }

    /**
     * @return null
     */
    public function getScopedEntityName()
    {
        return 'app';
    }

    /**
     * @return int
     */
    public function getScopeId()
    {
        return 0;
    }
}
