<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('banding_status_histories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('institution_id')->constrained('institutions')->cascadeOnDelete();
            $table->foreignUuid('banding_id')->constrained('bandings')->cascadeOnDelete();
            $table->string('status', 32);
            $table->text('catatan')->nullable();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['institution_id', 'banding_id'], 'banding_hist_banding_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('banding_status_histories');
    }
};
