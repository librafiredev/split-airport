<?php

namespace SplitAirport\Storage;

class Files
{
    private static $filesPath;

    public static function getFilesPath()
    {
        return self::$filesPath = get_template_directory() . '/includes/flightsUpdate/files';
    }


    public static function parseFiles(): array
    {

        $fligts = [];

        $files = array_diff(scandir(self::getFilesPath()), array('..', '.'));

        if ($files) {
            foreach ($files as $filePath) {

                $file = json_decode(file_get_contents(self::getFilesPath() . '/' . $filePath));

                if ($file) {
                    $fligts[basename($filePath, '.json')][] = $file;
                }
            }
        }

        return $fligts;
    }
}
