<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cakto_campaign_purchases', function (Blueprint $table) {
            $table->id();
            $table->string('cakto_purchase_id')->unique();
            $table->string('product_id')->index();
            $table->string('buyer_name')->nullable();
            $table->string('buyer_email')->nullable()->index();
            $table->string('status')->default('approved');
            $table->json('payload');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cakto_campaign_purchases');
    }
};
