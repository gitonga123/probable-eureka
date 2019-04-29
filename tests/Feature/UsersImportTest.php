<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Imports\UsersImport;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Controllers\UsersImportController;

class UsersImportTest extends TestCase
{
    /**
     * Test if the file is imported successfully
     * 
     * @return void
     */
    public function test_import()
    {
        Excel::fake();
        Excel::import(new UsersImport, 'export.csv');
        Excel::assertImported(
            'export.csv',
            function (UsersImport $import) {
                return true;
            }
        );
    }

    /**
     * Test if the import returns an array
     * 
     * @return void
     */
    public function test_import_array()
    {
        $import = new UsersImportController;

        $results = $import->import();
        $this->assertNotEmpty($results);
        $this->assertIsArray($results);
    }

    /**
     * Test if onboarding period count gets array counts
     * 
     * @return void
     */
    public function test_return_values_is_array()
    {
        $import = new UsersImportController;
        $import->setCreatedAtOnboarding();
        $this->assertIsArray($import->getOnboardingUniquePeriod());
        $created_at = $import->getOnboardingCreatedAtPeriod(
            $import->getOnboardingUniquePeriod()
        );
        $this->assertIsArray($created_at);
    }

    /**
     * Test the Json format of the return list
     * 
     * @return void
     */
    public function test_get_onboarding_period_count()
    {
        $this->get("/api/users_list")->assertSuccessful()->assertJsonStructure(
            [
                'success',
                'cohort'
            ]
        )->assertStatus(201);
    }
}
