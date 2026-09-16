<?php

declare(strict_types=1);

namespace Akeneo\Test\Platform\Unit\UIBundle;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;

/**
 * Guards the attribute type specific fields of the attribute creation page, previously covered by the Behat outline
 * display_available_field_options.feature:11. The page is defined only by configuration: the type to form map of
 * pim/attribute-edit-form/type-specific-form-registry (requirejs.yml), the form extension trees (form_extensions/**.yml,
 * merged by frontend/build/update-extensions.js) and the field labels (Structure jsmessages.en_US.yml). CI classifies
 * those files as backend changes, which skip the Playwright and Jest jobs, so this test parses them.
 *
 * @copyright 2026 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeTypeSpecificFormConfigTest extends TestCase
{
    private const string FORM_REGISTRY_MODULE = 'pim/attribute-edit-form/type-specific-form-registry';
    private const string STRUCTURE_JS_MESSAGES = 'src/Akeneo/Pim/Structure/Bundle/Resources/translations/jsmessages.en_US.yml';

    /** The fields of the removed Behat Examples table by attribute type, as field name => English label. */
    private const array SPECIFIC_FIELDS = [
        'pim_catalog_identifier' => [
            'max_characters' => 'Max characters',
            'validation_rule' => 'Validation rule',
        ],
        'pim_catalog_date' => [
            'date_min' => 'Min date',
            'date_max' => 'Max date',
        ],
        'pim_catalog_file' => [
            'max_file_size' => 'Max file size (MB)',
            'allowed_extensions' => 'Allowed extensions',
        ],
        'pim_catalog_image' => [
            'max_file_size' => 'Max file size (MB)',
            'allowed_extensions' => 'Allowed extensions',
        ],
        'pim_catalog_metric' => [
            'number_min' => 'Min number',
            'number_max' => 'Max number',
            'decimals_allowed' => 'Decimal values allowed',
            'negative_allowed' => 'Negative values allowed',
            'metric_family' => 'Measurement family',
        ],
        'pim_catalog_price_collection' => [
            'number_min' => 'Min number',
            'number_max' => 'Max number',
            'decimals_allowed' => 'Decimal values allowed',
        ],
        'pim_catalog_number' => [
            'number_min' => 'Min number',
            'number_max' => 'Max number',
            'decimals_allowed' => 'Decimal values allowed',
            'negative_allowed' => 'Negative values allowed',
        ],
        'pim_catalog_textarea' => [
            'max_characters' => 'Max characters',
            'wysiwyg_enabled' => 'Rich text editor enabled',
        ],
        'pim_catalog_text' => [
            'max_characters' => 'Max characters',
            'validation_rule' => 'Validation rule',
        ],
    ];

    /** @var array<string, array<string, mixed>>|null */
    private static ?array $extensions = null;

    /** @var array<string, array<string, string>>|null */
    private static ?array $formNames = null;

    /** @var array<string, string>|null */
    private static ?array $translations = null;

    public static function attributeTypes(): iterable
    {
        foreach (array_keys(self::SPECIFIC_FIELDS) as $type) {
            yield $type => [$type];
        }
    }

    /**
     * @dataProvider attributeTypes
     */
    public function test_the_create_form_of_the_type_shows_its_specific_fields_with_their_labels(string $type): void
    {
        $fields = $this->fieldsOfTheCreateForm($type);

        foreach (self::SPECIFIC_FIELDS[$type] as $fieldName => $expectedLabel) {
            $this->assertArrayHasKey(
                $fieldName,
                $fields,
                sprintf('The %s create form has no %s field. Its fields are: %s', $type, $fieldName, json_encode($fields)),
            );
            $this->assertSame($expectedLabel, $fields[$fieldName], sprintf('Wrong label for the %s field of %s.', $fieldName, $type));
        }
    }

    /**
     * @dataProvider attributeTypes
     */
    public function test_the_create_form_of_the_type_shows_no_field_specific_to_another_type(string $type): void
    {
        $specificFieldNames = array_keys(array_merge(...array_values(self::SPECIFIC_FIELDS)));
        $fieldNamesOfOtherTypes = array_diff($specificFieldNames, array_keys(self::SPECIFIC_FIELDS[$type]));

        $this->assertSame(
            [],
            array_values(array_intersect(array_keys($this->fieldsOfTheCreateForm($type)), $fieldNamesOfOtherTypes)),
            sprintf('The %s create form shows fields of other attribute types.', $type),
        );
    }

    public function test_every_registered_form_is_a_declared_form_extension(): void
    {
        $formNames = self::formNames();
        $this->assertNotEmpty($formNames);

        foreach ($formNames as $type => $forms) {
            foreach ($forms as $mode => $formName) {
                $this->assertArrayHasKey(
                    $formName,
                    self::extensions(),
                    sprintf('The %s form "%s" of %s is not declared in any form_extensions file.', $mode, $formName, $type),
                );
            }
        }
    }

    /**
     * @return array<string, string> the English label of each field reachable from the create form, by field name
     */
    private function fieldsOfTheCreateForm(string $type): array
    {
        $formName = self::formNames()[$type]['create'] ?? null;
        $this->assertIsString($formName, sprintf('No create form is registered for %s.', $type));
        $this->assertArrayHasKey($formName, self::extensions(), sprintf('The form "%s" is not declared.', $formName));

        $fields = [];
        foreach ($this->descendantsOf($formName) as $extensionCode) {
            $config = self::extensions()[$extensionCode]['config'] ?? [];
            if (!\is_array($config) || !isset($config['fieldName'])) {
                continue;
            }

            $this->assertIsString($config['label'] ?? null, sprintf('The field extension %s has no label.', $extensionCode));
            $this->assertArrayHasKey(
                $config['label'],
                self::translations(),
                sprintf('The label key "%s" of %s has no en_US translation.', $config['label'], $extensionCode),
            );
            $fields[$config['fieldName']] = self::translations()[$config['label']];
        }

        return $fields;
    }

    /**
     * @return string[]
     */
    private function descendantsOf(string $root): array
    {
        $children = [];
        foreach (self::extensions() as $code => $extension) {
            if (isset($extension['parent']) && \is_string($extension['parent'])) {
                $children[$extension['parent']][] = $code;
            }
        }

        $descendants = [];
        $toVisit = $children[$root] ?? [];
        while ([] !== $toVisit) {
            $code = array_shift($toVisit);
            if (isset($descendants[$code])) {
                continue;
            }
            $descendants[$code] = true;
            $toVisit = array_merge($toVisit, $children[$code] ?? []);
        }

        return array_keys($descendants);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private static function extensions(): array
    {
        if (null === self::$extensions) {
            // Same inputs as frontend/build/update-extensions.js: {form_extensions/**/*.yml,form_extensions.yml}.
            $files = (new Finder())
                ->files()
                ->in(self::rootDir() . '/src')
                ->exclude('node_modules')
                ->path('#Resources/config/form_extensions(/.+)?\.yml$#')
                ->sortByName();

            $extensions = [];
            foreach ($files as $file) {
                $content = Yaml::parseFile($file->getPathname());
                if (\is_array($content) && \is_array($content['extensions'] ?? null)) {
                    $extensions = array_replace_recursive($extensions, $content['extensions']);
                }
            }
            self::$extensions = $extensions;
        }

        return self::$extensions;
    }

    /**
     * @return array<string, array<string, string>>
     */
    private static function formNames(): array
    {
        if (null === self::$formNames) {
            $files = (new Finder())
                ->files()
                ->in(self::rootDir() . '/src')
                ->exclude('node_modules')
                ->path('#Resources/config/requirejs\.yml$#')
                ->sortByName();

            $formNames = [];
            foreach ($files as $file) {
                $content = Yaml::parseFile($file->getPathname());
                $registered = $content['config']['config'][self::FORM_REGISTRY_MODULE]['formNames'] ?? null;
                if (\is_array($registered)) {
                    $formNames = array_replace_recursive($formNames, $registered);
                }
            }
            self::$formNames = $formNames;
        }

        return self::$formNames;
    }

    /**
     * @return array<string, string> the en_US messages keyed by their full dotted key
     */
    private static function translations(): array
    {
        if (null === self::$translations) {
            self::$translations = self::flatten(Yaml::parseFile(self::rootDir() . '/' . self::STRUCTURE_JS_MESSAGES));
        }

        return self::$translations;
    }

    /**
     * @param array<mixed> $messages
     *
     * @return array<string, string>
     */
    private static function flatten(array $messages, string $prefix = ''): array
    {
        $flattened = [];
        foreach ($messages as $key => $message) {
            $fullKey = '' === $prefix ? (string) $key : $prefix . '.' . $key;
            if (\is_array($message)) {
                $flattened = array_merge($flattened, self::flatten($message, $fullKey));
            } else {
                $flattened[$fullKey] = (string) $message;
            }
        }

        return $flattened;
    }

    private static function rootDir(): string
    {
        return \dirname(__DIR__, 5);
    }
}
