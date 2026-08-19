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
            $table->integer('updated_num')->default(0)->nullable();
            $table->integer('updated_year')->nullable();
        });
    }
}
