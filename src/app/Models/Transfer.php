<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transfer extends Model
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'payer',
        'payee',
        'value',
    ];

    public function payerUser()
    {
        return $this->belongsTo(User::class, 'payer');
    }

    public function payeeUser()
    {
        return $this->belongsTo(User::class, 'payee');
    }
}
