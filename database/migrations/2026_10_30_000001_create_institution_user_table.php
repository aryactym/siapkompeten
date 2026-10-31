<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('institution_user', function (Blueprint $table) {
            $table->foreignUuid('institution_id')->constrained('institutions')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('role', 30)->nullable();
            $table->timestamps();

            $table->primary(['institution_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('institution_user');
    }
};
