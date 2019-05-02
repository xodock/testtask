<?php


namespace App\CsvImportModule;


interface CsvDataImporter
{
    /**
     * @param CsvExtractedData $csvExtractedData
     * @param string $entityClassName
     * @param CsvColumnMapper|null $columnMapper
     *
     */
    static function import(CsvExtractedData $csvExtractedData, string $entityClassName, CsvColumnMapper $columnMapper): void;
}
