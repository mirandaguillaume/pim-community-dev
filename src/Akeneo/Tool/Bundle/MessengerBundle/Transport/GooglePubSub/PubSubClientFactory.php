<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MessengerBundle\Transport\GooglePubSub;

use Google\Cloud\Core\AnonymousCredentials;
use Google\Cloud\PubSub\PubSubClient;

/**
 * Factory to create the Google PubSubClient.
 *
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PubSubClientFactory
{
    private ?string $keyFilePath = null;

    public function __construct(string $keyFilePath)
    {
        if ($keyFilePath !== '' && $keyFilePath !== '0') {
            $this->keyFilePath = $keyFilePath;
        }
    }

    public function createPubSubClient(array $config): PubSubClient
    {
        $defaults = ['transport' => 'rest'];

        if (null !== $this->keyFilePath) {
            $defaults['keyFilePath'] = $this->keyFilePath;
        } elseif ($this->isEmulatorMode()) {
            $defaults['credentials'] = new AnonymousCredentials();
        }

        return new PubSubClient(array_merge($defaults, $config));
    }

    private function isEmulatorMode(): bool
    {
        return !(in_array(getenv('PUBSUB_EMULATOR_HOST'), ['', '0'], true) || getenv('PUBSUB_EMULATOR_HOST') === [] || getenv('PUBSUB_EMULATOR_HOST') === false)
            || !empty($_ENV['PUBSUB_EMULATOR_HOST'] ?? '');
    }
}
