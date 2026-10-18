<?php

namespace App\Observers;

use App\Models\AuditLog;
use App\Models\Customer;
use Illuminate\Support\Facades\Log;

class CustomerObserver
{
    /**
     * Handle the Customer "created" event.
     */
    public function created(Customer $customer): void
    {
        AuditLog::log('created', $customer, [], $customer->toArray(), 'Customer created');
        Log::info("Customer {$customer->id} created.");
    }

    /**
     * Handle the Customer "updated" event.
     */
    public function updated(Customer $customer): void
    {
        AuditLog::log('updated', $customer, $customer->getOriginal(), $customer->toArray(), 'Customer updated');
    }

    /**
     * Handle the Customer "deleted" event.
     */
    public function deleted(Customer $customer): void
    {
        AuditLog::log('deleted', $customer, $customer->toArray(), [], 'Customer deleted');
        Log::info("Customer {$customer->id} deleted.");
    }
}
