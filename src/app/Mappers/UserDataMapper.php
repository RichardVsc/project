<?php

namespace App\Mappers;

use App\Models\User;
use App\Data\UserData;

class UserDataMapper
{
    /**
     * Create a UserData instance from a User Eloquent model.
     *
     * This method converts an Eloquent User model into a UserData
     * value object, encapsulating only the necessary user data
     * for domain-level operations.
     *
     * @param User $user The Eloquent user model instance.
     * @return UserData The corresponding UserData instance.
     */
    public function fromModel(User $user): UserData
    {
        return new UserData(
            id: $user->id,
            user_type: $user->user_type,
            balance: (int) $user->balance
        );
    }
}
