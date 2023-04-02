<?php

namespace ArchCrudLaravel\App\Models;

use ArchCrudLaravel\App\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ArchModel extends BaseModel
{
    use HasFactory;

    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = 'updated_at';
    public const DELETED_AT = 'deleted_at';

    public $table = 'arch';
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

    protected array $parentKeys = [];

    public function addParentKey(string $parentKey)
    {
        if (!in_array($parentKey, $this->parentKeys)) {
            $this->parentKeys[] = $parentKey;
        }
    }

    public function removeParentKey(string $parentKey)
    {
        $key = array_search($parentKey, $this->parentKeys);
        if ($key !== false) {
            unset($this->parentKeys[$key]);
        }
    }

    public function columnExists(string $columnName): bool
    {
        return Schema::hasColumn($this->table, $columnName);
    }
}
