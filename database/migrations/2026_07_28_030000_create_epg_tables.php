<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('epg_sources', function (Blueprint $table) {
            $table->id(); $table->string('name'); $table->text('url'); $table->boolean('is_active')->default(true); $table->timestamp('last_synced_at')->nullable(); $table->text('last_error')->nullable(); $table->timestamps();
        });
        Schema::create('epg_channels', function (Blueprint $table) {
            $table->id(); $table->foreignId('epg_source_id')->constrained('epg_sources')->cascadeOnDelete(); $table->string('xmltv_id'); $table->string('name'); $table->string('icon_url')->nullable(); $table->timestamps(); $table->unique(['epg_source_id','xmltv_id']);
        });
        Schema::create('tv_channel_epgs', function (Blueprint $table) {
            $table->id(); $table->foreignId('tv_channel_id')->unique()->constrained('tv_channels')->cascadeOnDelete(); $table->foreignId('epg_source_id')->constrained('epg_sources')->cascadeOnDelete(); $table->foreignId('epg_channel_id')->nullable()->constrained('epg_channels')->nullOnDelete(); $table->timestamps();
        });
        Schema::create('epg_programs', function (Blueprint $table) {
            $table->id(); $table->foreignId('epg_source_id')->constrained('epg_sources')->cascadeOnDelete(); $table->foreignId('tv_channel_id')->constrained('tv_channels')->cascadeOnDelete(); $table->string('xmltv_channel_id'); $table->string('title'); $table->text('description')->nullable(); $table->string('category')->nullable(); $table->string('icon_url')->nullable(); $table->dateTime('starts_at'); $table->dateTime('ends_at'); $table->timestamps();
            $table->unique(['epg_source_id','xmltv_channel_id','starts_at','ends_at'], 'epg_program_unique'); $table->index(['tv_channel_id','starts_at','ends_at']);
        });
        DB::table('epg_sources')->insert(['name'=>'GlobeTV Brasil (XMLTV)','url'=>'https://raw.githubusercontent.com/globetvapp/epg/main/Brazil/brazil1.xml','is_active'=>true,'created_at'=>now(),'updated_at'=>now()]);
    }
    public function down(): void { Schema::dropIfExists('epg_programs'); Schema::dropIfExists('tv_channel_epgs'); Schema::dropIfExists('epg_channels'); Schema::dropIfExists('epg_sources'); }
};
