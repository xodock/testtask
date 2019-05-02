<?php


namespace App\CsvImportModule;


interface CsvExtractedData
{
    /**
     * CsvExtractedData constructor.
     * @param array $dataArray
     * @param array $columnHeadersRowArray
     */
    function __construct(array $dataArray, array $columnHeadersRowArray = []);

    /**
     * @param array $dataArray
     */
    function setDataArray(array $dataArray): void;

    /**
     * @param array $columnHeadersRowArray
     */
    function setColumnHeadersRowArray(array $columnHeadersRowArray): void;

    /**
     * @return array
     */
    function getColumnHeadersRowArray(): array;

    /**
     * @return array
     */
    function getDataArray(): array;
}
