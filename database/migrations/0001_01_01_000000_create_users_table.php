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
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('institution_id')->nullable()->constrained('institutions')->restrictOnDelete();
            $table->string('name', 150);
            $table->string('username', 60)->nullable();
            $table->text('nik')->nullable();
            $table->char('nik_hash', 64)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('phone', 20)->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('status', 20)->default('aktif');
            $table->string('blocked_reason')->nullable();
            $table->boolean('is_locked')->default(false);
            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip', 45)->nullable();
            $table->string('tenant_key', 36)->virtualAs("COALESCE(institution_id, 'platform')");
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenant_key', 'email']);
            $table->unique(['tenant_key', 'username']);
            $table->unique(['tenant_key', 'nik_hash']);
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->uuid('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
