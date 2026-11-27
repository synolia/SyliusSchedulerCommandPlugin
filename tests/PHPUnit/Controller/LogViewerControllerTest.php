<?php

declare(strict_types=1);

namespace Tests\Synolia\SyliusSchedulerCommandPlugin\PHPUnit\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Synolia\SyliusSchedulerCommandPlugin\Controller\LogViewerController;
use Synolia\SyliusSchedulerCommandPlugin\Entity\ScheduledCommand;
use Tests\Synolia\SyliusSchedulerCommandPlugin\PHPUnit\CollectDeprecationsTrait;

final class LogViewerControllerTest extends KernelTestCase
{
    use CollectDeprecationsTrait;

    private EntityManagerInterface $entityManager;

    private ScheduledCommand $scheduledCommand;

    private string $logFilePath;

    protected function setUp(): void
    {
        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $this->entityManager->beginTransaction();

        $logFile = \uniqid('log-viewer-test-', true) . '.log';
        $this->logFilePath = self::getContainer()->getParameter('kernel.logs_dir') . \DIRECTORY_SEPARATOR . $logFile;
        (new Filesystem())->dumpFile($this->logFilePath, "first line\nsecond line\n");

        $this->scheduledCommand = (new ScheduledCommand())
            ->setName('Log viewer test')
            ->setCommand('about')
            ->setLogFile($logFile)
        ;
        $this->entityManager->persist($this->scheduledCommand);
        $this->entityManager->flush();
    }

    protected function tearDown(): void
    {
        $this->entityManager->rollback();
        @\unlink($this->logFilePath);

        parent::tearDown();
    }

    /** @dataProvider refreshQueryProvider */
    public function testRefreshReadsItsOptionsFromTheQueryString(array $query, array $expectedLines): void
    {
        $request = Request::create('/scheduled-commands/get-log.json', 'GET', $query);

        $response = null;
        $deprecations = $this->collectDeprecations(function () use ($request, &$response): void {
            $response = self::getContainer()->get(LogViewerController::class)
                ->getLogs($request, (string) $this->scheduledCommand->getId());
        });

        // Request::get() is deprecated in Symfony 7.4 and removed in Symfony 8.
        self::assertSame([], array_values(array_filter(
            $deprecations,
            static fn (string $message): bool => str_contains($message, 'Request::get()'),
        )));
        self::assertInstanceOf(JsonResponse::class, $response);
        $content = \json_decode((string) $response->getContent(), true);
        self::assertSame(\filesize($this->logFilePath), $content['size']);
        self::assertSame($expectedLines, array_values($content['data']));
    }

    public static function refreshQueryProvider(): iterable
    {
        yield 'grep' => [['refresh' => '1', 'lastsize' => '0', 'grep-keywords' => 'second', 'invert' => '0'], ['second line']];
        yield 'inverted grep' => [['refresh' => '1', 'lastsize' => '0', 'grep-keywords' => 'second', 'invert' => '1'], ['first line']];
        yield 'already fetched' => [['refresh' => '1', 'lastsize' => '23', 'grep-keywords' => '', 'invert' => '0'], []];
    }
}
