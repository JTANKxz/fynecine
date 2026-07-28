<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void { Schema::dropIfExists('channel_watch_party_members'); Schema::dropIfExists('channel_watch_parties'); }
 public function down(): void { }
};
