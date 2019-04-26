<?php

namespace App\Http\Controllers;

use App\Imports\UsersImport;
use Carbon\CarbonImmutable;

class UsersImportController extends Controller
{
    protected $users;

    /**
     * Import the CSV File
     *
     * @return void
     */
    public function import()
    {

        $users = (new UsersImport)->toArray('export.csv');

        return $users;
    }

    /**
     * Get the Onboarding Period and the Percentange
     *
     * @return void
     */
    public function setOnboardingCreatedAt()
    {
        $list = $this->import();
        $users = [];
        for ($i = 0; $i < count($list[0]); $i++) {
            $users[] = array(
                "created_at" => CarbonImmutable::parse(
                    $list[0][$i]['created_at']
                )->week(),
                "onboarding_perentage" => $list[0][$i]['onboarding_perentage']
            );
        }
        $this->users = $users;
    }
    /**
     * Get the Onboarding Perioud count per week
     *
     * @return void
     */
    public function getOnboardingPeriodCount()
    {
        $this->setOnboardingCreatedAt();
        $created_at = $this->getOnboardingCreatedAtPeriod(
            $this->getOnboardingUniquePeriod()
        );

        $created_at = array_map(
            array($this, 'getCountIndividualWeek'),
            $created_at
        );

        return response()->json(
            ['success' => true, 'cohort' => $created_at],
            201
        );
    }

    /**
     * Get the Unique Periods of account creation
     *
     * @return void
     */
    public function getOnboardingUniquePeriod(): array
    {
        $created_at = [];
        for ($i = 0; $i < count($this->users); $i++) {
            $created_at[] = $this->users[$i]['created_at'];
        }

        return array_unique($created_at);
    }

    /**
     * Get the number of users and the corresponding percentages
     * For the users created for that particulator period
     *
     * @param array $list // An array of period of creation and the percentage
     * 
     * @return array
     */
    public function getOnboardingCreatedAtPeriod(array $list): array
    {
        $created_at = [];
        foreach ($list as $key => $value) {
            for ($i = 0; $i < count($this->users); $i++) {
                if ($this->users[$i]['created_at'] == $value) {
                    $created_at[$value][] = intval(
                        $this->users[$i]['onboarding_perentage']
                    );
                }
            }
        }
        return $created_at;
    }

    /**
     * Get number of onboarding percentages grouped on similarity
     *
     * @param array $list // contains the list of onboarding percetages
     * 
     * @return array
     */
    public function getCountIndividualWeek(array $list): array
    {
        $list = array_count_values($list);
        krsort($list);
        return  $list;
    }
}
