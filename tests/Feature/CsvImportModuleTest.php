<?php

namespace Tests\Feature;

use App\Client;
use App\CsvImportModule\CsvColumnMapper;
use App\CsvImportModule\CsvDataImporter;
use App\CsvImportModule\CsvExtractedData;
use App\CsvImportModule\CsvReader;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CsvImportModuleTest extends TestCase
{
    use WithFaker, RefreshDatabase;

    const CSV_FILE_NAME = 'test.csv';

    /** @var array $testDataArray Test dataset should stored here */
    private $testDataArray = [];

    public function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
    }

    /**
     * Generates new test dataset
     */
    protected function createNewTestDataArray(): void {
        $newTestDataArray = [];
        for ($i = 0; $i < 10; $i += 1) {
            $newTestDataArray[] = $this->getNewTestDataArrayEntry();
        }
        $this->setTestDataArray($newTestDataArray);
    }

    /**
     * Generates array with single csv row data
     * @return array
     */
    protected function getNewTestDataArrayEntry(): array {
        return [$this->faker->email, $this->faker->firstName, $this->faker->lastName];
    }

    /**
     * Get array with test dataset
     * @return array
     */
    protected function getTestDataArray(): array {
        return $this->testDataArray;
    }

    /**
     * Set array with test dataset
     * @param $testDataArray
     */
    protected function setTestDataArray($testDataArray): void {
        $this->testDataArray = $testDataArray;
    }

    /**
     * @param bool $withHeadersRow
     * @return CsvExtractedData
     */
    protected function getMockCsvExtractedData(bool $withHeadersRow = false): CsvExtractedData {
        /** @var CsvExtractedData $mockedCsvExtractedData */
        $mockedCsvExtractedDataInterface = $this->mock(CsvExtractedData::class, function ($mock) use ($withHeadersRow) {
            $mock->shouldReceive('getDataArray')->andReturn($this->getTestDataArray());
            $mock->shouldReceive('getColumnHeadersRowArray')->andReturn($withHeadersRow ? ['email', 'firstName', 'lastName'] : []);
        });
        return $mockedCsvExtractedDataInterface;
    }

    /**
     * @param CsvExtractedData $csvExtractedData
     * @return CsvReader
     */
    protected function getMockCsvReader(CsvExtractedData $csvExtractedData): CsvReader {
        /** @var CsvReader $mockedCsvReader */
        $mockedCsvReaderInterface = $this->mock(CsvReader::class, function ($mock) use ($csvExtractedData) {
            $mock->shouldReceive('readCsvFile')->andReturn($csvExtractedData);
        });
        return $mockedCsvReaderInterface;
    }

    /**
     *  Check import API with data mapper created from csv headers stored in ExtractedData instance
     */
    public function testImportWithMapperCreatedFromExtractedDataHeadersArray() {

        $this->createNewTestDataArray();

        /** @var CsvExtractedData $mockedCsvExtractedData */
        $mockedCsvExtractedDataInterface = $this->getMockCsvExtractedData(true);

        /** @var CsvReader $mockedCsvReader */
        $mockedCsvReaderInterface = $this->getMockCsvReader($mockedCsvExtractedDataInterface);

        /** @var CsvExtractedData $csvExtractedData */
        $csvExtractedData = $mockedCsvReaderInterface::readCsvFile(self::CSV_FILE_NAME);

        /** @var CsvColumnMapper $csvColumnMapper */
        $csvColumnMapper = CsvColumnMapper::create($csvExtractedData->getColumnHeadersRowArray());

        CsvDataImporter::import($csvExtractedData, Client::class, $csvColumnMapper);

        foreach ($csvExtractedData->getDataArray() as $extractedItem){
            $this->assertDatabaseHas('clients', [
                'email' => $extractedItem[0],
                'firstName' => $extractedItem[1],
                'lastName' => $extractedItem[2],
            ]);
        }
    }

    /**
     * Check import API with data custom mapper, where lastName should be imported from firstName csv column, and vice versa
     */
    public function testImportWithManuallyPassedMapperWithReplacedColumns() {

        $this->createNewTestDataArray();

        /** @var CsvExtractedData $mockedCsvExtractedData */
        $mockedCsvExtractedDataInterface = $this->getMockCsvExtractedData(false);

        /** @var CsvReader $mockedCsvReader */
        $mockedCsvReaderInterface = $this->getMockCsvReader($mockedCsvExtractedDataInterface);

        /** @var CsvExtractedData $csvExtractedData */
        $csvExtractedData = $mockedCsvReaderInterface::readCsvFile(self::CSV_FILE_NAME);

        /** @var CsvColumnMapper $csvColumnMapper */
        $csvColumnMapper = CsvColumnMapper::create(['email', 'lastName', 'firstName']);

        CsvDataImporter::import($csvExtractedData, Client::class, $csvColumnMapper);

        foreach ($csvExtractedData->getDataArray() as $extractedItem){
            $this->assertDatabaseHas('clients', [
                'email' => $extractedItem[0],
                'firstName' => $extractedItem[2],
                'lastName' => $extractedItem[1],
            ]);
        }
    }

    /**
     * Check import API with data custom mapper, where last column in csv should be ignored
     */
    public function testImportWithManuallyPassedMapperWithOmittedLastColumn() {

        $this->createNewTestDataArray();

        /** @var CsvExtractedData $mockedCsvExtractedData */
        $mockedCsvExtractedDataInterface = $this->getMockCsvExtractedData(false);

        /** @var CsvReader $mockedCsvReader */
        $mockedCsvReaderInterface = $this->getMockCsvReader($mockedCsvExtractedDataInterface);

        /** @var CsvExtractedData $csvExtractedData */
        $csvExtractedData = $mockedCsvReaderInterface::readCsvFile(self::CSV_FILE_NAME);

        /** @var CsvColumnMapper $csvColumnMapper */
        $csvColumnMapper = CsvColumnMapper::create(['email', 'firstName']);

        CsvDataImporter::import($csvExtractedData, Client::class, $csvColumnMapper);

        foreach ($csvExtractedData->getDataArray() as $extractedItem) {
            $this->assertDatabaseHas('clients', [
                'email' => $extractedItem[0],
                'firstName' => $extractedItem[1],
                'lastName' => null,
            ]);
        }
    }

    /**
     * Check import API with data custom mapper, where column between first and last in csv should be ignored
     */
    public function testImportWithManuallyPassedMapperWithOmittedSecondColumn() {

        $this->createNewTestDataArray();

        /** @var CsvExtractedData $mockedCsvExtractedData */
        $mockedCsvExtractedDataInterface = $this->getMockCsvExtractedData(false);

        /** @var CsvReader $mockedCsvReader */
        $mockedCsvReaderInterface = $this->getMockCsvReader($mockedCsvExtractedDataInterface);

        /** @var CsvExtractedData $csvExtractedData */
        $csvExtractedData = $mockedCsvReaderInterface::readCsvFile(self::CSV_FILE_NAME);

        /** @var CsvColumnMapper $csvColumnMapper */
        $csvColumnMapper = CsvColumnMapper::create(['email', null, 'firstName']);

        CsvDataImporter::import($csvExtractedData, Client::class, $csvColumnMapper);

        foreach ($csvExtractedData->getDataArray() as $extractedItem) {
            $this->assertDatabaseHas('clients', [
                'email' => $extractedItem[0],
                'firstName' => $extractedItem[2],
                'lastName' => null,
            ]);
        }
    }

}
