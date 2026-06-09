<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /* Configure the Users table to add a role column */
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'role')) {
                $table->enum('role', ['admin', 'user'])->default('user')->after('password');
            }
        });

        /* Create the Equipment table */
        Schema::create('equipment', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('serial_number')->unique();
            $table->string('category');
            $table->enum('status', ['available', 'reserved', 'maintenance'])->default('available');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->date('due_date')->nullable();
            $table->timestamps();
        });

        /* Create a Reservations table */
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipment_id')->constrained('equipment')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservations');
        Schema::dropIfExists('equipment');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};
