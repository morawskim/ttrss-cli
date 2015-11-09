<?php

namespace ttrssCli\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use ttrssCli\Services\Helper;
use ttrssCli\Services\TTRss;

class ChangeUserEmail extends Command
{
    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this
            ->setName('ttrss:change-user-email')
            ->setDescription('Change user email.')
            ->addArgument(
                'login',
                InputArgument::REQUIRED,
                'User login name'
            )
            ->addArgument(
                'email',
                InputArgument::REQUIRED,
                'User new email address'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $ttrssDir = Helper::getTtRssDir($input);
        if (empty($ttrssDir)) {
            throw new \RuntimeException(sprintf('Dir where tt-rss is stored not set'));
        }

        $service = new TTRss($ttrssDir);
        $service->init();

        $login = $input->getArgument('login');
        $email = $input->getArgument('email');

        $service->changeUserEmail($login, $email);
    }
}