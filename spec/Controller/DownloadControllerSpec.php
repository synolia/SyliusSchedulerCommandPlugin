<?php

declare(strict_types=1);

namespace spec\Synolia\SyliusSchedulerCommandPlugin\Controller;

use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpFoundation\Response;
use Synolia\SyliusSchedulerCommandPlugin\Controller\DownloadController;
use Synolia\SyliusSchedulerCommandPlugin\Entity\ScheduledCommand;
use Synolia\SyliusSchedulerCommandPlugin\Repository\ScheduledCommandRepositoryInterface;

final class DownloadControllerSpec extends ObjectBehavior
{
    function let(ScheduledCommandRepositoryInterface $scheduledCommandRepository)
    {
        $this->beConstructedWith($scheduledCommandRepository, 'logDir');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(DownloadController::class);
    }

    function it_allows_to_download_a_log_file(ScheduledCommandRepositoryInterface $scheduledCommandRepository)
    {
        $command = 'about';
        $scheduledCommand = new ScheduledCommand();
        $scheduledCommand->setName('Displays project information')
            ->setCommand($command);

        $scheduledCommandRepository->find($command)->willReturn($scheduledCommand);

        $this->downloadLogFile($command)->shouldReturnAnInstanceOf(Response::class);
    }
}
