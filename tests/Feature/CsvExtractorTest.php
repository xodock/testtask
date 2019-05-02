<?php

namespace Tests\Feature;

use App\CsvImportModule\CsvExtractedData;
use App\CsvImportModule\CsvReader;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CsvExtractorTest extends TestCase
{

    const CSV_FILE_WITH_HEADERS_COLUMN_NAME = 'testCsvWithHeaders.csv';
    const CSV_FILE_WITHOUT_HEADERS_COLUMN_NAME = 'testCsvWithoutHeaders.csv';
    const CSV_DATA_CONTENTS = [
        ['mertz.rocky@nicolas.com', 'Lysanne', 'Tromp'],
        ['rkihn@rau.com', 'Audie', 'Nolan'],
        ['hammes.billy@zieme.com', 'Lawrence', 'Hyatt']
    ];
    const CSV_HEADERS = ['email', 'firstName', 'lastName'];

    /**
     * Create CSV text from CSV_DATA_CONTENTS constant
     * @return string
     */
    public function generateCsvFileContent() {
        $rows = array_map(function ($row) {
            return implode(',', $row);
        }, self::CSV_DATA_CONTENTS);
        return implode("\n", $rows);
    }

    /**
     * Create CSV text from CSV_HEADERS constant
     * @return string
     */
    public function generateCsvFileHeaderRow() {
        return implode(',', self::CSV_HEADERS);
    }


    /**
     * Create CSV text with headers and content
     * @return string
     */
    public function createCsvWithHeaders() {
        return $this->generateCsvFileHeaderRow()."\n".$this->generateCsvFileContent();
    }
    /**
     * Create CSV text only with content
     * @see generateCsvFileContent
     * @return string
     */
    public function createCsvWithoutHeaders() {
        return $this->generateCsvFileContent();
    }

    /**
     * Assert data extracted from file equals data in CSV_DATA_CONTENTS
     * @param CsvExtractedData $csvExtractedData
     */
    public function assertExtractedDataEqualsExpectedData(CsvExtractedData $csvExtractedData) {
        foreach ($csvExtractedData->getDataArray() as $extractedItemIndex => $extractedItem) {
            foreach ($extractedItem as $extractedItemFieldIndex => $extractedItemField) {
                $this->assertEquals(self::CSV_DATA_CONTENTS[$extractedItemIndex][$extractedItemFieldIndex], $extractedItemField);
            }
        }
    }

    /**
     * Check data extracts correctly from file with header row
     */
    public function testCsvFileExtractionWithHeadersRow() {
        Storage::fake();
        Storage::put(self::CSV_FILE_WITH_HEADERS_COLUMN_NAME, $this->createCsvWithHeaders());
        Storage::assertExists(self::CSV_FILE_WITH_HEADERS_COLUMN_NAME);

        /** @var CsvExtractedData $csvExtractedData */
        $csvExtractedData = CsvReader::readCsvFile(self::CSV_FILE_WITH_HEADERS_COLUMN_NAME, true);

        $this->assertExtractedDataEqualsExpectedData($csvExtractedData);
        $this->assertEquals(self::CSV_HEADERS[0], $csvExtractedData->getColumnHeadersRowArray()[0]);
        $this->assertEquals(self::CSV_HEADERS[1], $csvExtractedData->getColumnHeadersRowArray()[1]);
        $this->assertEquals(self::CSV_HEADERS[2], $csvExtractedData->getColumnHeadersRowArray()[2]);
    }


    /**
     * Check data extracts correctly from file without header row
     */
    public function testCsvFileExtractionWithoutHeadersRow() {
        Storage::fake();
        Storage::put(self::CSV_FILE_WITHOUT_HEADERS_COLUMN_NAME, $this->createCsvWithHeaders());
        Storage::assertExists(self::CSV_FILE_WITHOUT_HEADERS_COLUMN_NAME);

        /** @var CsvExtractedData $csvExtractedData */
        $csvExtractedData = CsvReader::readCsvFile(self::CSV_FILE_WITHOUT_HEADERS_COLUMN_NAME, false);

        $this->assertExtractedDataEqualsExpectedData($csvExtractedData);

        $this->assertEmpty($csvExtractedData->getColumnHeadersRowArray());
    }
}
