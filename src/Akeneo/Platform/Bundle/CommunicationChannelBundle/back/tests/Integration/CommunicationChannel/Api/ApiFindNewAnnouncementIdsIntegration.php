<?php

declare(strict_types=1);

namespace Akeneo\Platform\CommunicationChannel\Test\Integration\Delivery\InternalApi\Announcement;

use Akeneo\Platform\CommunicationChannel\Domain\Announcement\Model\Read\AnnouncementItem;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\ServerException;
use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Process\Process;

class ApiFindNewAnnouncementIdsIntegration extends KernelTestCase
{

    private \Symfony\Component\Process\Process $process;

    public function setUp(): void
    {
        parent::setUp();
        static::bootKernel(['debug' => false]);

        $configDir = __DIR__;
        $this->process = Process::fromShellCommandline("./vendor/bin/phiremock --config-path '$configDir'");
        $this->process->start();
        $this->waitServerUp();
    }

    public function tearDown(): void
    {
        $this->process->stop(3, SIGKILL);
        $this->waitServerDown();

        parent::tearDown();
    }

    public function test_it_finds_new_announcement_ids()
    {
        $query = self::getContainer()->get('akeneo_communication_channel.query.api.find_new_announcement_ids');
        $result = $query->find('Serenity', '2020105', 'en_US');
        Assert::assertCount(4, $result);
        Assert::assertEquals(
            [
                'update_1-duplicate-a-product_2020-07',
                'update_2-option-screen-revamp_2020-07',
                'update_3-rules-updates_2020-07',
                'update_4-manually-execute-naming-conventions-on-assets_2020-07'
            ],
            $result
        );
    }

    public function waitServerUp()
    {
        $attempt = 0;
        do {
            try {
                $httpClient = new Client(['base_uri' => self::getContainer()->getParameter('comm_panel_api_url')]);
                $httpClient->get('/');
            } catch (ConnectException) {
                usleep(100000);
            } catch (ClientException | ServerException) {
                return; // started
            }

            $attempt++;
        } while ($attempt < 30);

        throw new \RuntimeException('Impossible to start the mock HTTP server.');
    }

    private function waitServerDown(): void
    {
        $attempt = 0;
        do {
            try {
                $httpClient = new Client(['base_uri' => self::getContainer()->getParameter('comm_panel_api_url')]);
                $httpClient->get('/', ['timeout' => 0.5, 'connect_timeout' => 0.5]);
            } catch (ConnectException) {
                return;
            } catch (\Throwable) {
                // Server still responding, wait
            }
            usleep(200000);
            $attempt++;
        } while ($attempt < 15);
    }
}
