<?php

namespace SplitAirport\Storage;

class Files
{

    public static function getFilesPath()
    {
        return get_template_directory() . '/includes/flightsUpdate/files';
    }

    public static function manageUpdateFiles($file, $fileName)
    {
        if ($file) {

            if (!file_exists(self::getFilesPath())) {
                mkdir(self::getFilesPath(), 0755, true);
            }

            return file_put_contents(self::getFilesPath() . '/' . $fileName, $file);
        }

        return false;
    }


    public static function parseFiles(): array
    {

        $fligts = [];

        $files = array_diff(scandir(self::getFilesPath()), array('..', '.'));

        if ($files) {
            foreach ($files as $filePath) {

                $file = json_decode(file_get_contents(self::getFilesPath() . '/' . $filePath));

                if ($file) {
                    $fligts[basename($filePath, '.json')] = $file;
                }
            }
        }

        return $fligts;
    }
}
