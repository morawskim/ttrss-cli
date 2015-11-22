<?php

namespace ttrssCli\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use ttrssCli\Services\Helper;
use ttrssCli\Services\TTRss;

class SendMail extends Command
{
    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this
            ->setName('ttrss:send-test-mail')
            ->setDescription('Send test mail to user. Check SMTP settings.')
            ->addArgument(
                'email',
                InputArgument::REQUIRED,
                'Email address'
            )
            ->addOption(
                'name',
                'f',
                InputOption::VALUE_OPTIONAL,
                'Name'
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

        $email = $input->getArgument('email');
        if ($input->hasOption('name')) {
            $name = $input->getOption('name');
        } else {
            $name = null;
        }

        $service->sendTestEmail($email, $name);
    }


}