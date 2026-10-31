<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('import_rows', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('institution_id')->constrained('institutions')->cascadeOnDelete();
            $table->foreignUuid('import_batch_id')->constrained('import_batches')->cascadeOnDelete();
            $table->unsignedInteger('row_number');
            $table->json('data');
            $table->json('errors')->nullable();
            $table->string('status', 20)->default('valid');
            $table->foreignUuid('asesi_id')->nullable()->constrained('asesis')->nullOnDelete();
            $table->timestamps();

            $table->unique(['import_batch_id', 'row_number']);
            $table->index(['institution_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('import_rows');
    }
};
