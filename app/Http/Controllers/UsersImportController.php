<?php

namespace App\Http\Controllers;

use App\Imports\UsersImport;
use Carbon\CarbonImmutable;

class UsersImportController extends Controller
{
    /**
     * Array containing created at and corresponding percentages only
     *
     * @var array
     */
    protected $create_onboarding = [];

    /**
     * Import the CSV File
     *
     * @return array
     */
    public function import(): array
    {

        $users_list = (new UsersImport)->toArray('export.csv');

        return $users_list;
    }

    /**
     * Get the Created at Periods and Onboarding Percentages into an array
     *
     * @return void
     */
    public function setOnboardingCreatedAt()
    {
        $list = $this->import();
        $create_onboarding = [];
        for ($i = 0; $i < count($list[0]); $i++) {
            $create_onboarding[] = array(
                "created_at" => CarbonImmutable::parse(
                    $list[0][$i]['created_at']
                )->week(),
                "onboarding_percentage" => $list[0][$i]['onboarding_percentage']
            );
        }
        $this->create_onboarding = $create_onboarding;
    }

    /**
     * Get the list of users with created at and onboarding percentage
     *
     * @return array
     */
    public function getOnboardingCreatedAtList(): array
    {
        return $this->create_onboarding;
    }

    /**
     * Return a json response of weekly onboarding percentages
     *
     * @return \Illuminate\Http\Response
     */
    public function getPeriodCountOnboardingPercentage()
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
        for ($i = 0; $i < count($this->create_onboarding); $i++) {
            $created_at[] = $this->create_onboarding[$i]['created_at'];
        }

        return array_unique($created_at);
    }

    /**
     * Get the list of onboarding percentages for every unique week
     *
     * @param array $list // An array of period of creation and the percentage
     *
     * @return array
     */
    public function getOnboardingCreatedAtPeriod(array $list): array
    {
        $created_at = [];
        foreach ($list as $key => $value) {
            for ($i = 0; $i < count($this->create_onboarding); $i++) {
                if ($this->create_onboarding[$i]['created_at'] == $value) {
                    $created_at[$value][] = intval(
                        $this->create_onboarding[$i]['onboarding_percentage']
                    );
                }
            }
        }

        return $created_at;
    }

    /**
     * Get the number of created at percentages per weekly cohort
     *
     * @param array $list // contains the list of onboarding percentages weekly
     *
     * @return array
     */
    public function getCountIndividualWeek(array $list): array
    {
        $list = array_count_values($list);
        ksort($list);
        $totals = array_sum($list);
        for ($i = 0; $i < count($list); $i++) {
            if (!empty(array_slice($list, $i))) {
                $total[] = intval(
                    number_format(
                        (
                            array_sum(array_slice($list, $i)) / $totals
                        ) * 100, 0
                    )
                );
            }
        }
        return  $total;
    }
}
