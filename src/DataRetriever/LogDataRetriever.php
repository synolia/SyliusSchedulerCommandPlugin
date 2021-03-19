<?php

declare(strict_types=1);

namespace Synolia\SyliusSchedulerCommandPlugin\DataRetriever;

use Symfony\Component\Filesystem\Exception\FileNotFoundException;

final class LogDataRetriever
{
    /** @var int Maximum amount of bytes this application can load into memory. Default is 2 Megabyte = 2097152 byte */
    private $maxSizeToLoad = 2097152;

    public function getLog(
        string $filePath,
        int $lastFetchedSize = 0,
        string $grepKeyword = '',
        bool $invertGrep = false
    ): array {
        /** Clear the stat cache to get the latest results */
        \clearstatcache(true, $filePath);

        $filesize = \filesize($filePath);
        if (!is_numeric($filesize)) {
            throw new FileNotFoundException('Log file could not be found.');
        }

        $maxLength = ($filesize - $lastFetchedSize);

        /**
         * Verify that we don't load more data then allowed.
         */
        if ($maxLength > $this->maxSizeToLoad) {
            $maxLength = ($this->maxSizeToLoad / 2);
        }

        /**
         * Actually load the data
         */
        $data = [];
        if ($maxLength > 0) {
            $filePointer = \fopen($filePath, 'rb');

            if (false === $filePointer) {
                throw new FileNotFoundException('Could not load data for the log file.');
            }

            \fseek($filePointer, (int) -$maxLength, \SEEK_END);
            $data = \explode("\n", (string) \fread($filePointer, (int) $maxLength));
        }
        $data = $this->grepData($data, $grepKeyword, $invertGrep);

        /**
         * If the last entry in the array is an empty string lets remove it.
         */
        if (\end($data) === '') {
            \array_pop($data);
        }

        return [
            'size' => $filesize,
            'file' => $filePath,
            'data' => $data,
        ];
    }

    /**
     * Run the grep function to return only the lines we're interested in.
     */
    private function grepData(array $data, string $grepKeyword, bool $invertGrep): array
    {
        if (false === $invertGrep) {
            $lines = preg_grep("/$grepKeyword/", $data);

            return \is_array($lines) ? $lines : [];
        }

        $lines = preg_grep("/$grepKeyword/", $data, \PREG_GREP_INVERT);

        return \is_array($lines) ? $lines : [];
    }
}
