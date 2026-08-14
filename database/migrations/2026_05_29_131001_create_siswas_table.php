<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('siswas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('orang_tua_id')->nullable()->constrained('orang_tuas')->nullOnDelete();
            $table->foreignId('kelas_id')->nullable()->constrained('kelas')->nullOnDelete();
            $table->foreignId('ekstrakurikuler_id')->nullable()->constrained('ekstrakurikulers')->cascadeOnDelete();
            $table->string('nisn')->unique();
            $table->string('nama_siswa')->nullable();
            $table->enum('jenis_kelamin', ['Laki-laki', 'Perempuan']);
            $table->string('tempat_lahir');
            $table->date('tgl_lahir');
            $table->string('agama');
            $table->string('nomor_wa')->nullable();
            $table->text('alamat')->nullable();
            $table->date('tgl_masuk')->nullable();
            $table->string('status')->nullable();
            $table->string('foto')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('siswas');
    }
};
