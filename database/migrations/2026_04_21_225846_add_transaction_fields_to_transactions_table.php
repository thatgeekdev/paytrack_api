<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->uuid('uuid')->unique()->after('id');
            $table->string('reference')->unique()->after('uuid');
            $table->string('idempotency_key')->nullable()->index();
            $table->enum('status', ['pending', 'success', 'failed'])->default('pending');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            //
        });
    }
};
