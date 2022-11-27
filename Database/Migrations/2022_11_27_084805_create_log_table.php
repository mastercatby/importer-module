<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $primaryKeyName = 'id';
    
        if (!Schema::hasTable('importer_log')) {
            Schema::create('importer_log',
                function (Blueprint $table) use ($primaryKeyName) {
                      $table->increments($primaryKeyName);
                });
        }

        $columns = Schema::getColumnListing('importer_log');

        Schema::table('importer_log',
            function (Blueprint $table) use ($columns, $primaryKeyName) {

                if (!in_array($primaryKeyName, $columns)) {
                    $table->increments($primaryKeyName);
                }
                if (!in_array('type', $columns)) {
                    $table->integer('type')->unsigned()->default(0);
                }
                if (!in_array('run_at', $columns)) {
                    $table->date('run_at')->useCurrent();
                }
                if (!in_array('entries_processed', $columns)) {
                    $table->integer('entries_processed')->unsigned()->default(0);
                }
                if (!in_array('entries_created', $columns)) {
                    $table->integer('entries_created')->unsigned()->default(0);
                }
                if (!in_array('entries_skipped', $columns)) {
                    $table->integer('entries_skipped')->unsigned()->default(0);
                }
                if (!in_array('errors', $columns)) {
                    $table->integer('errors')->unsigned()->default(0);
                }

        });
    }

    /**
     * Reverse the migration.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('importer_log');
    }
}
