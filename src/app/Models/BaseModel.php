<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BaseModel extends Model
{
    use HasFactory;

    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = 'updated_at';
    public const DELETED_AT = 'deleted_at';

    public $timestamps = false;

    protected $hidden = [
        self::CREATED_AT,
        self::UPDATED_AT
    ];
    protected $fillable = [self::DELETED_AT];

    public static $snakeAttributes = false;
    public $guardFromUpdate = [
        'id',
        self::CREATED_AT,
        self::UPDATED_AT,
        self::DELETED_AT
    ];
}
