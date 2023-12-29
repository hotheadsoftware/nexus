<?php

namespace App\Providers;

use App\Models\Subscriber;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use Spark\Plan;
use Spark\Spark;

class SparkServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Spark::billable(Subscriber::class)->resolve(function (Request $request) {
            return $request->user();
        });

        Spark::billable(Subscriber::class)->authorize(function (Subscriber $billable, Request $request) {
            return $request->user() &&
                $request->user()->id == $billable->id;
        });

        Spark::billable(Subscriber::class)->checkPlanEligibility(function (Subscriber $billable, Plan $plan) {
            // if ($billable->projects > 5 && $plan->name == 'Basic') {
            //     throw ValidationException::withMessages([
            //         'plan' => 'You have too many projects for the selected plan.'
            //     ]);
            // }
        });
    }
}
