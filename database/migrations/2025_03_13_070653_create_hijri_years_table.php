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
        Schema::create('hijri_years', function (Blueprint $table) {
            $table->id();
            $table->string('year')->unique(); 
            $table->date('start_date');
            $table->date('end_date')->nullable();
            
            $table->boolean('is_active')->default(false); 
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hijri_years');
    }
};
