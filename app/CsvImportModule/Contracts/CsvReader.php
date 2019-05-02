<?php


namespace App\CsvImportModule;


interface CsvReader
{
    /**
     * @param string $fileName
     * @param bool $readFirstRowAsColumnHeaders
     * @return CsvExtractedData
     */
    static function readCsvFile(string $fileName, bool $readFirstRowAsColumnHeaders = false): CsvExtractedData;
}
