<?php

namespace ttrssCli\Services;

use Symfony\Component\Console\Input\InputInterface;

class Helper
{
    public static function getTtRssDir(InputInterface $input)
    {
        $ttrssDir = null;
        if ($input->hasOption('tt-rss')) {
            $ttrssDir = $input->getOption('tt-rss');
        }
        if (empty($ttrssDir)) {
            $ttrssDir = getenv('TTRSS_DIR');
        }

        return $ttrssDir;
    }
}