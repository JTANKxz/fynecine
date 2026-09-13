<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('championships', function (Blueprint $table) {
            $table->string('image_url', 1000)->nullable()->after('provider_color');
        });

        DB::table('championships')
            ->where('external_provider', '365scores')
            ->where('external_id', '113')
            ->update([
                'image_url' => 'https://imagecache.365scores.com/image/upload/f_png,w_64,h_64,c_limit,q_auto:eco,dpr_2,d_Countries:Round:21.png/v11/Competitions/113',
            ]);
    }

    public function down(): void
    {
        Schema::table('championships', function (Blueprint $table) {
            $table->dropColumn('image_url');
        });
    }
};
