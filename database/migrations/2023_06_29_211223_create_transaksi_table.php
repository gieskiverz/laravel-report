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
        Schema::create('transaksis', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->unsignedBigInteger('coa_id'); //fk
            $table->string('desc')->nullable();
            $table->decimal('debit', 65, 0)->default(0);
            $table->decimal('credit', 65, 0)->default(0);
            $table->timestamps();

            //define fk
            $table->foreign('coa_id')->references('id')->on('coas')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksis');
    }
};
