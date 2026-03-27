<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('banned_devices', function (Blueprint $table) {
            $table->id();
            $table->string('ip_address')->nullable()->index();
            $table->string('device_id')->nullable()->index();
            $table->text('ban_reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('banned_devices');
    }
};
