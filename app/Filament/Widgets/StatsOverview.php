<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use App\Models\Listing;
use App\Models\Transaction;
use Illuminate\Support\Number;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class StatsOverview extends BaseWidget
{
    private function getPercentage(int $from, int $to) {
        return $to - $from / ($to + $from / 2) * 100;
    }

    protected function getStats(): array
    {
        $newListing = Listing::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->count();
        $transactions = Transaction::whereStatus('approved')->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year);
        $prevTransactions = Transaction::whereStatus('approved')->whereMonth('created_at', Carbon::now()->subMonth()->month)
            ->whereYear('created_at', Carbon::now()->subMonth()->year);
        $transactionPercentage = $this->getPercentage($prevTransactions->count(), $transactions->count());
        $revenuePercentage = $this->getPercentage($prevTransactions->sum('total_price'), $transactions->sum('total_price'));

        return [
            Stat::make('New Listing of the month', $newListing),

            Stat::make('Transaction of the month', $transactions->count())
                ->description($transactionPercentage > 0 ? "{$transactionPercentage}% increase" : "{$transactionPercentage}% decrease")
                ->descriptionIcon($transactionPercentage > 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($transactionPercentage > 0 ? 'success' : 'danger'),

            Stat::make('Revenue of the month', Number::currency($transactions->sum('total_price'), 'USD'))
                ->description($revenuePercentage > 0 ? "{$revenuePercentage}% increase" : "{$revenuePercentage}% decrease")
                ->descriptionIcon($revenuePercentage > 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($revenuePercentage > 0 ? 'success' : 'danger'),
        ];
    }
}
