<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method \Illuminate\Database\Eloquent\Relations\BelongsTo payerUser()
 * @method \Illuminate\Database\Eloquent\Relations\BelongsTo payeeUser()
 */
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

    /**
     * Get the payer user associated with the transfer.
     */
    public function payerUser()
    {
        return $this->belongsTo(User::class, 'payer');
    }

    /**
     * Get the payee user associated with the transfer.
     */
    public function payeeUser()
    {
        return $this->belongsTo(User::class, 'payee');
    }
}
