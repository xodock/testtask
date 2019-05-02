<?php


namespace App\CsvImportModule;


interface CsvColumnMapper
{
    /**
     * CsvColumnMapper constructor.
     * @param array $map
     * @return CsvColumnMapper
     */
    static function create(array $map): CsvColumnMapper;

    /**
     * @param array $columnMap
     */
    function setColumnMap(array $columnMap): void;

    /**
     * @return array
     */
    function getColumnMap(): array;

    /**
     * @param string $name
     * @return int
     */
    function getColumnIndexByName(string $name): int;
}
