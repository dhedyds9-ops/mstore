<?php

namespace App\Observers;

use App\Models\User;
use App\Services\EmployeeSyncService;

class UserObserver
{
    public function created(User $user): void
    {
        $allowCreate = request()->boolean('register_as_employee');
        app(EmployeeSyncService::class)->syncFromUser($user, $allowCreate);
    }

    public function updated(User $user): void
    {
        $allowCreate = request()->boolean('register_as_employee');
        app(EmployeeSyncService::class)->syncFromUser($user, $allowCreate);
    }

    public function deleted(User $user): void
    {
        app(EmployeeSyncService::class)->unlinkUser($user->id);
    }
}
