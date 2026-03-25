<?php

namespace App\Filament\Widgets\Analytics\Concerns;

use Illuminate\Support\Carbon;

/**
 * Trait for adding filtering capability to analytics widgets
 * Provides date range and age group filtering
 */
trait HasAnalyticsFilters
{
    /**
     * Get the date range filter from request
     */
    protected function getDateRange(): array
    {
        $filter = request()->get('date_range', 'last_30_days');
        
        return match($filter) {
            'last_7_days' => [
                'start' => Carbon::now()->subDays(7)->startOfDay(),
                'end' => Carbon::now()->endOfDay(),
            ],
            'last_30_days' => [
                'start' => Carbon::now()->subDays(30)->startOfDay(),
                'end' => Carbon::now()->endOfDay(),
            ],
            'last_90_days' => [
                'start' => Carbon::now()->subDays(90)->startOfDay(),
                'end' => Carbon::now()->endOfDay(),
            ],
            'last_6_months' => [
                'start' => Carbon::now()->subMonths(6)->startOfDay(),
                'end' => Carbon::now()->endOfDay(),
            ],
            'last_year' => [
                'start' => Carbon::now()->subYear()->startOfDay(),
                'end' => Carbon::now()->endOfDay(),
            ],
            'all_time' => [
                'start' => null,
                'end' => null,
            ],
            default => [
                'start' => Carbon::now()->subDays(30)->startOfDay(),
                'end' => Carbon::now()->endOfDay(),
            ],
        };
    }

    /**
     * Get the age group filter from request
     */
    protected function getAgeGroupFilter(): ?string
    {
        return request()->get('age_group');
    }

    /**
     * Get the profession filter from request
     */
    protected function getProfessionFilter(): ?string
    {
        return request()->get('profession');
    }

    /**
     * Apply filters to a query - safely handles missing columns
     */
    protected function applyFilters($query): \Illuminate\Database\Eloquent\Builder
    {
        // Check if decision_made_at column exists before filtering by date
        $hasDateColumn = \Illuminate\Support\Facades\Schema::hasColumn('decision_logs', 'decision_made_at');
        
        if ($hasDateColumn) {
            $dateRange = $this->getDateRange();
            
            if ($dateRange['start'] && $dateRange['end']) {
                $query->whereBetween('decision_made_at', [$dateRange['start'], $dateRange['end']]);
            }
        }
        
        // Age group filter
        $hasAgeGroupColumn = \Illuminate\Support\Facades\Schema::hasColumn('decision_logs', 'age_group');
        if ($hasAgeGroupColumn) {
            $ageGroup = $this->getAgeGroupFilter();
            if ($ageGroup) {
                $query->where('age_group', $ageGroup);
            }
        }
        
        // Profession filter
        $hasProfessionColumn = \Illuminate\Support\Facades\Schema::hasColumn('decision_logs', 'profession');
        if ($hasProfessionColumn) {
            $profession = $this->getProfessionFilter();
            if ($profession) {
                $query->where('profession', $profession);
            }
        }
        
        return $query;
    }

    /**
     * Get filter options for the widget
     */
    public function getFilterOptions(): array
    {
        return [
            'date_range' => [
                'last_7_days' => 'Last 7 Days',
                'last_30_days' => 'Last 30 Days',
                'last_90_days' => 'Last 90 Days',
                'last_6_months' => 'Last 6 Months',
                'last_year' => 'Last Year',
                'all_time' => 'All Time',
            ],
            'age_group' => [
                '' => 'All Age Groups',
                'child' => 'Child',
                'teenager' => 'Teenager',
                'adult' => 'Adult',
                'old' => 'Old',
            ],
            'profession' => [
                '' => 'All Professions',
                'doctor' => 'Doctor',
                'engineer' => 'Engineer',
                'teacher' => 'Teacher',
                'artist' => 'Artist',
                'business' => 'Business',
                'lawyer' => 'Lawyer',
                'scientist' => 'Scientist',
            ],
        ];
    }
}