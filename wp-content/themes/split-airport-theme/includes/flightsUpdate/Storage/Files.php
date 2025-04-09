<?php

namespace SplitAirport\Storage;

class Files
{
    private static $filesPath;

    public static function getFilesPath()
    {
        return self::$filesPath = get_template_directory() . '/includes/flightsUpdate/files';
    }

    public static function manageUpdateFiles($file, $fileName)
    {
        if ($file) {
            
            if (!file_exists(self::$filesPath)) {
                mkdir(self::$filesPath, 0755, true); 
            }

            return file_put_contents(self::$filesPath . '/' . $fileName, $file);
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
                    $fligts[basename($filePath, '.json')][] = $file;
                }
            }
        }

        return $fligts;
    }
}
