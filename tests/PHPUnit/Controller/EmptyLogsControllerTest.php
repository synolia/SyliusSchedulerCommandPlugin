<?php

declare(strict_types=1);

namespace Tests\Synolia\SyliusSchedulerCommandPlugin\PHPUnit\Controller;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Synolia\SyliusSchedulerCommandPlugin\Controller\EmptyLogsController;
use Synolia\SyliusSchedulerCommandPlugin\Entity\ScheduledCommand;
use Tests\Synolia\SyliusSchedulerCommandPlugin\PHPUnit\CollectDeprecationsTrait;

final class EmptyLogsControllerTest extends KernelTestCase
{
    use CollectDeprecationsTrait;

    private EntityManagerInterface $entityManager;

    private ScheduledCommand $scheduledCommand;

    private string $logFilePath;

    protected function setUp(): void
    {
        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $this->entityManager->beginTransaction();

        $logFile = \uniqid('empty-logs-test-', true) . '.log';
        // SYNOLIA_SCHEDULER_PLUGIN_LOGS_DIR defaults to the kernel logs directory.
        $this->logFilePath = self::getContainer()->getParameter('kernel.logs_dir') . \DIRECTORY_SEPARATOR . $logFile;
        (new Filesystem())->dumpFile($this->logFilePath, "some output\n");

        $this->scheduledCommand = (new ScheduledCommand())
            ->setName('Empty logs test')
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

    #[DataProvider('methodProvider')]
    public function testEmptiesTheLogFilesOfTheGivenIds(string $method): void
    {
        $request = Request::create('/scheduled-commands/empty-logs', $method, ['ids' => [(string) $this->scheduledCommand->getId()]]);
        $request->setSession(new Session(new MockArraySessionStorage()));
        self::getContainer()->get(RequestStack::class)->push($request);

        $response = null;
        $deprecations = $this->collectDeprecations(function () use ($request, &$response): void {
            $response = self::getContainer()->get(EmptyLogsController::class)->emptyLogs($request);
        });

        // Request::get() is deprecated in Symfony 7.4 and removed in Symfony 8.
        self::assertSame([], array_values(array_filter(
            $deprecations,
            static fn (string $message): bool => str_contains($message, 'Request::get()'),
        )));
        self::assertInstanceOf(Response::class, $response);
        self::assertTrue($response->isRedirect());
        self::assertSame('', \file_get_contents($this->logFilePath));
    }

    public static function methodProvider(): iterable
    {
        yield 'ids in the query string' => ['GET'];
        yield 'ids in the request body' => ['PUT'];
    }
}
