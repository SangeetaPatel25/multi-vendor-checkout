<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class AdminPolicy
{
    /**
     * Determine whether the user can access admin features.
     */
    public function accessAdmin(User $user): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can view orders.
     */
    public function viewOrders(User $user): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can view order details.
     */
    public function viewOrder(User $user): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can view vendors.
     */
    public function viewVendors(User $user): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can view customers.
     */
    public function viewCustomers(User $user): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can view statistics.
     */
    public function viewStats(User $user): bool
    {
        return $user->role === 'admin';
    }
}
