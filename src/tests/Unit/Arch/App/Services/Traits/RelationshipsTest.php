<?php

namespace Tests\Unit\App\Services\Traits;

use ArchCrudLaravel\App\Models\Tests\{
    RelationsModel,
    TestsModel
};
use ArchCrudLaravel\App\Providers\ArchProvider;
use ArchCrudLaravel\App\Services\Traits\Relationships;
use ArchCrudLaravel\Tests\Traits\RemoveMigrations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{
    BelongsTo,
    BelongsToMany,
    HasMany,
    HasManyThrough,
    HasOne,
    HasOneThrough,
    MorphMany,
    MorphOne,
    MorphTo,
    MorphToMany,
    Relation
};
use Illuminate\Foundation\Testing\DatabaseMigrations;
use ReflectionClass;
use Tests\TestCase;

class RelationshipsTest extends TestCase
{
    use Relationships, DatabaseMigrations, RemoveMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('vendor:publish', [
            '--provider' => ArchProvider::class,
            '--tag' => 'migrations'
        ]);
        $this->artisan('migrate');

        // Configuração inicial
        $this->nameModel = TestsModel::class;
        $this->model = new $this->nameModel;
        $this->request = [
            'key' => 'test key show',
            'value' => 'test value show'
        ];
        $this->relationships = ['relation'];

        $this->testModel = $this->nameModel::create($this->request);
        RelationsModel::create(['test_id' => $this->testModel->id]);
        $this->relationModel = RelationsModel::find($this->testModel->id);
    }

    protected function tearDown(): void
    {
        $migrator = app('migrator');
        $migrator->rollback([database_path('migrations')]);
        $this->removeMigrations();
        parent::tearDown();
    }

    public function testHasRelationships()
    {
        $model = new TestsModel();
        $result = $this->hasRelationships($model);
        $this->assertFalse($result);

        $result = $this->hasRelationships($this->testModel);

        $this->assertTrue($result);
    }

    public function testGetRelationshipNames()
    {
        $model = new TestsModel();
        $names = $this->getRelationshipNames($model);

        $expectedNames = [
            'relation'
        ];

        $this->assertEquals($expectedNames, $names);
    }

    public function testIsSupportedRelation()
    {
        $model = new TestsModel();

        $relation = $model->hasOne(RelationsModel::class);
        $result = $this->isSupportedRelation($relation);
        $this->assertTrue($result);

        $relation = $model->hasMany(RelationsModel::class);
        $result = $this->isSupportedRelation($relation);
        $this->assertTrue($result);

        $relation = $model->morphOne(RelationsModel::class, 'relationable');
        $result = $this->isSupportedRelation($relation);
        $this->assertTrue($result);

        $relation = $model->morphMany(RelationsModel::class, 'relationable');
        $result = $this->isSupportedRelation($relation);
        $this->assertTrue($result);

        $relation = $model->belongsTo(RelationsModel::class);
        $result = $this->isSupportedRelation($relation);
        $this->assertFalse($result);

        $relation = $model->belongsToMany(RelationsModel::class);
        $result = $this->isSupportedRelation($relation);
        $this->assertFalse($result);

        $relation = $model->hasOneThrough(RelationsModel::class, TestsModel::class);
        $result = $this->isSupportedRelation($relation);
        $this->assertFalse($result);
    }

    public function testIsModelVisited()
    {
        $model = new TestsModel();
        $model->id = 1;

        $result = $this->isModelVisited($model);
        $this->assertFalse($result);

        $this->markModelVisited($model);

        $result = $this->isModelVisited($model);
        $this->assertTrue($result);
    }
}
