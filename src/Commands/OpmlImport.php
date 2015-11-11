<?php

namespace ttrssCli\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use ttrssCli\Services\Helper;
use ttrssCli\Services\TTRss;

class OpmlImport extends Command
{
    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this
            ->setName('ttrss:opml-import')
            ->setDescription('Import opml')
            ->addArgument(
                'login',
                InputArgument::REQUIRED,
                'User login name'
            )
            ->addArgument(
                'file',
                InputArgument::REQUIRED,
                'Path to opml file'
            );
    }

    /**
     * Executes the current command.
     *
     * This method is not abstract because you can use this class
     * as a concrete class. In this case, instead of defining the
     * execute() method, you set the code to execute by passing
     * a Closure to the setCode() method.
     *
     * @param InputInterface $input An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     *
     * @return null|int null or 0 if everything went fine, or an error code
     *
     * @throws \LogicException When this abstract method is not implemented
     *
     * @see setCode()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $ttrssDir = Helper::getTtRssDir($input);
        if (empty($ttrssDir)) {
            throw new \RuntimeException(sprintf('Dir where tt-rss is stored not set'));
        }

        $service = new TTRss($ttrssDir);
        $service->init();

        $login = $input->getArgument('login');
        $file = $input->getArgument('file');

        $opml = $service->importOpml($login, $file);
        $output->writeln($opml, OutputInterface::VERBOSITY_NORMAL);
    }
}