<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RevenueExpenses extends Model
{
    use HasFactory;
    protected $table = 'revenue_expenses';
    protected $casts = [
        'bill' => 'json',
    ];
}
