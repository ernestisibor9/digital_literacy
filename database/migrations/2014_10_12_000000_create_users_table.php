<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('firstname')->nullable();
            $table->string('lastname')->nullable();
            $table->string('middlename')->nullable();
            $table->string('member_id')->default(DB::raw("CONCAT('MEM-', LPAD(FLOOR(RAND() * 9999), 4, '0'))"));
            $table->string('phone')->nullable();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->date('date_of_birth')->nullable();
            $table->string('marital_status')->nullable();
            $table->string('whatsapp')->nullable();
            $table->enum('gender', ['male', 'female'])->nullable();
            $table->enum('role', ['admin', 'user', 'instructor'])->default('user')->nullable();
            $table->text('residential_address')->nullable();
            $table->string('country')->nullable();
            $table->string('state')->nullable();
            $table->string('lga')->nullable();
            $table->string('occupation')->nullable();
            $table->string('occupation_name')->nullable();
            $table->text('occupation_address')->nullable();
            $table->string('next_of_kin')->nullable();
            $table->string('next_of_kin_relationship')->nullable();
            $table->text('next_of_kin_address')->nullable();
            $table->string('next_of_kin_phone_number')->nullable();
            $table->enum('subscription_status', ['active', 'inactive', 'expired'])->default('inactive');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
