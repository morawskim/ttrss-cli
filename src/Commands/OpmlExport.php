<?php

namespace ttrssCli\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use ttrssCli\Services\Helper;
use ttrssCli\Services\TTRss;

class OpmlExport extends Command
{
    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this
            ->setName('ttrss:opml-export')
            ->setDescription('Export user subscribed feeds.')
            ->addArgument(
                'login',
                InputArgument::REQUIRED,
                'User login name'
            )
            ->addOption(
                'file',
                'f',
                InputArgument::OPTIONAL,
                'Output to file',
                '/dev/fd/1'
            )
            ->addOption(
                'show-settings',
                's',
                InputArgument::OPTIONAL,
                'Output to file',
                0
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
        $file = $input->getOption('file');

        $opml = $service->exportOpml($login, intval($input->getOption('show-settings')));

        if (is_writable(dirname($file))) {
            //overwrite existing file
            $bytes = file_put_contents($file, $opml);
            if (false === $bytes) {
                throw new \RuntimeException(sprintf('Cant save opml to file "%s"', $file));
            }
        } else {
            throw new \RuntimeException(sprintf('File "%s" is not writable', $file));
        }
    }
}