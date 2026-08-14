<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class textendkontrak extends Migration
{
    protected $tableName = "t_extend_kontrak";
    
    public function up()
    {
        Schema::table($this->tableName, function (Blueprint $table) {
            //$table->string('_existColumnName_')->change();
            //$table->string('_columnName_');
            //$table->dropColumn([ ]);
            //$table->bigInteger('m_kary_det_kontrak_id')->comment('{"src":"m_kary_det_kontrak.id"}')->nullable();
            //$table->string('nomor')->nullable();
        });
    }
}
