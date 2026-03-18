<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('experiment_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('experiment_id')->constrained()->cascadeOnDelete();
            $table->string('key', 20);
            $table->unsignedInteger('weight')->default(50);
            $table->timestamps();

            $table->unique(['experiment_id', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('experiment_variants');
    }
};
