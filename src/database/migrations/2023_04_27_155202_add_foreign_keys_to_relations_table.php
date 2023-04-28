<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAddForeignKeysToTestsRelationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tests_relations', function (Blueprint $table) {
            $table->foreign(['test_id'], 'testsRelations_testId_foreign')
                ->references(['id'])
                ->on('tests')
                ->onUpdate('NO ACTION')
                ->onDelete('NO ACTION');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tests_relations', function (Blueprint $table) {
            $table->dropForeign('testsRelations_testId_foreign');
        });
    }
};
