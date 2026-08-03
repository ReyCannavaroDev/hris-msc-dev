<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class mkary extends Migration
{
    protected $tableName = "m_kary";
    
    public function up()
    {
        Schema::table($this->tableName, function (Blueprint $table) {
            //$table->string('_existColumnName_')->change();
            //$table->string('_columnName_');
            // $table->dropColumn(['ig','x','facebook','linkedin','email']);
            // $table->boolean('can_outscope')->default(false)->nullable();
            //$table->bigInteger('tipe_karyawan_id')->comment('{"src":"m_general.id"}')->nullable();
            // $table->string('ig',100)->nullable();
            // $table->string('x',100)->nullable();
            // $table->string('facebook',100)->nullable();
            // $table->string('linkedin',100)->nullable();
            // $table->string('email',100)->nullable();
        });
    }
}
