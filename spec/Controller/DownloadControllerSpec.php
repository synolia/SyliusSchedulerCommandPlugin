<?php

namespace spec\Synolia\SchedulerCommandPlugin\Controller;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use spec\Synolia\SchedulerCommandPlugin\Entity\ScheduledCommandSpec;
use Symfony\Component\HttpFoundation\Response;
use Synolia\SchedulerCommandPlugin\Controller\DownloadController;
use Synolia\SchedulerCommandPlugin\Entity\ScheduledCommand;
use Synolia\SchedulerCommandPlugin\Repository\ScheduledCommandRepository;

class DownloadControllerSpec extends ObjectBehavior
{
    function let(ScheduledCommandRepository $scheduledCommandRepository)
    {
        $this->beConstructedWith($scheduledCommandRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(DownloadController::class);
    }

    function it_allows_to_download_a_log_file(ScheduledCommandRepository $scheduledCommandRepository)
    {
        $command = "about";
        $scheduledCommand = new ScheduledCommand();
        $scheduledCommand->setName("Displays project information")
            ->setCommand($command);

        $scheduledCommandRepository->find($command)->willReturn($scheduledCommand);

        $this->downloadLogFile($command)->shouldReturnAnInstanceOf(Response::class);
    }
}
