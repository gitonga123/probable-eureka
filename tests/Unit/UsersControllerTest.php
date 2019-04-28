<?php

namespace Tests\Unit;

use Tests\TestCase;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\UsersImport;
use App\Http\Controllers\UsersImportController;
use Carbon\CarbonImmutable;

class UsersControllerTest extends TestCase
{
    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function test_import_file()
    {
        $this->withoutExceptionHandling();
        Excel::fake();
        Excel::import(new UsersImport, 'export.csv');
        Excel::assertImported(
            'export.csv',
            function (UsersImport $import) {
                return true;
            }
        );
        $users = (new UsersImport)->toArray('export.csv');
        $this->assertIsArray($users);
        $user_import = new UsersImportController;
        $users_ = $user_import->import();

        $this->assertEquals($users, $users_);
    }

    /**
     * Test if the array conataines created at
     * 
     * @return void
     */
    public function test_onboarding_created_at_list()
    {
        $this->withoutExceptionHandling();
        $user_import = new UsersImportController;
        $list = $user_import->import();
        $users = [];
        for ($i = 0; $i < count($list[0]); $i++) {
            $users[] = array(
                "created_at" => CarbonImmutable::parse(
                    $list[0][$i]['created_at']
                )->week(),
                "onboarding_percentage" => $list[0][$i]['onboarding_percentage']
            );
        }
        $user_import->setOnboardingCreatedAt();
        $users_ = $user_import->getOnboardingCreatedAtList();
        $this->assertNotNull($users_);
        $this->assertEquals($users, $users_);
    }

    /**
     * Test unique date of account creation for users weekely
     * Test if array contains unique date of creation
     * 
     * @return void
     */
    public function test_created_at_unique_for_users()
    {
        $this->withoutExceptionHandling();
        $user_import = new UsersImportController;
        $user_import->import();
        $user_import->setOnboardingCreatedAt();
        $users_ = $user_import->getOnboardingCreatedAtList();
        for ($i = 0; $i < count($users_); $i++) {
            $created_at[] = $users_[$i]['created_at'];
        }

        $created_at = array_unique($created_at);
        $this->assertIsArray($created_at);
        $this->assertIsArray(
            $user_import->getOnboardingUniquePeriod(),
            "Expects An Array Return"
        );
        $this->assertEquals($created_at, $user_import->getOnboardingUniquePeriod());
        $this->assertCount(
            count($created_at),
            $user_import->getOnboardingUniquePeriod(),
            "Weekely Cohorts is not equal to" . count($created_at)
        );
    }


    /**
     * Test grouping of weekely cohorts
     * 
     * @return void
     */
    public function test_weekely_cohort_listing()
    {
        $this->withoutExceptionHandling();
        $created_at = [];
        $user_import = new UsersImportController;
        $user_import->import();
        $user_import->setOnboardingCreatedAt();
        $users = $user_import->getOnboardingCreatedAtList();
        $users_ = $user_import->getOnboardingUniquePeriod();
        $this->assertIsArray($users_);


        foreach ($users_ as $key => $value) {
            for ($i = 0; $i < count($users); $i++) {
                if ($users[$i]['created_at'] == $value) {
                    $created_at[$value][] = intval(
                        $users[$i]['onboarding_percentage']
                    );
                }
            }
        }
        $created_at_keys = array_keys($created_at);
        $this->assertIsArray($created_at);
        $users_created_at = $user_import->getOnboardingCreatedAtPeriod($users_);
        $this->assertIsArray($users_created_at, "Expected an array return");
        $this->assertEquals(
            $created_at,
            $users_created_at,
            "Expected Equal number of weekely cohorts"
        );

        for ($i = 0; $i < count($created_at_keys); $i++) {
            $this->arrayHasKey($created_at_keys[$i], $users_created_at);
        }
    }

    /**
     * Test if the number of % calculated are correct
     * 
     * @return void
     */
    public function test_calculated_weekely_user_percentage_value()
    {
        $this->withoutExceptionHandling();
        $new_list = [];
        $user_import = new UsersImportController;
        $user_import->import();
        $user_import->setOnboardingCreatedAt();
        $users_created_at = $user_import->getOnboardingCreatedAtPeriod(
            $user_import->getOnboardingUniquePeriod()
        );
        $this->assertIsArray($users_created_at);
        foreach ($users_created_at as $key => $value) {
            $new_list[$key] = $user_import->getCountIndividualWeek($value);
        }

        $this->assertNotEquals($users_created_at, $new_list);
        $this->get("/api/users_list")->assertSuccessful()->assertJsonStructure(
            [
                'success',
                'cohort'
            ]
        )->assertStatus(201);
        $this->get("/api/users_list")->assertJson(
            [
                'success' => true,
                'cohort' => $new_list
            ]
        );
    }
}
