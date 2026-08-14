<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class presensiabsensi extends Migration
{
    protected $tableName = "presensi_absensi";
    
    public function up()
    {
        Schema::table($this->tableName, function (Blueprint $table) {
            if (!Schema::hasColumn($this->tableName, 'istirahat_tipe')) {
                $table->string('istirahat_tipe', 50)->nullable(); // KELUAR, DI_KANTOR
                $table->time('istirahat_start')->nullable();
                $table->time('istirahat_end')->nullable();
                $table->integer('istirahat_durasi')->nullable(); // Dalam menit
            }
        });
    }
}