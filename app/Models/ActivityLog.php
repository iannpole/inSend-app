<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ActivityLog extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'activity_logs';

    protected $fillable = [
        'user_id',
        'user_name',
        'action', // create, update, delete
        'model_type', // Product, Recipe, Order
        'model_name', // name of the item
        'details', // any extra info
    ];

    public static function log(string $action, string $modelType, string $modelName, ?string $details = null)
    {
        $user = Auth::user();
        
        return self::create([
            'user_id' => $user ? $user->id : null,
            'user_name' => $user ? $user->name : 'System/Guest',
            'action' => $action,
            'model_type' => $modelType,
            'model_name' => $modelName,
            'details' => $details,
        ]);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
