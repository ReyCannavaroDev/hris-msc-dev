<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class tperhitungangaji extends Migration
{
    protected $tableName = "t_perhitungan_gaji";
    
    public function up()
    {
        Schema::table($this->tableName, function (Blueprint $table) {
            //$table->string('_existColumnName_')->change();
            //$table->string('_columnName_');
            //$table->dropColumn([ ]);
            // $table->decimal('total_potongan',22,2)->nullable()->default(0)->change();
            // $table->decimal('total_bonus',22,2)->nullable()->default(0)->change();
        });
    }
}
