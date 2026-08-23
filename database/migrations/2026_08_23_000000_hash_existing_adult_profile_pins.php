<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // O schema antigo limitava o PIN a quatro caracteres. O bcrypt
        // precisa de espaço para aproximadamente 60 caracteres.
        Schema::table('profiles', function (Blueprint $table) {
            $table->string('adult_pin', 255)->nullable()->change();
        });

        DB::table('profiles')
            ->whereNotNull('adult_pin')
            ->where('adult_pin', '!=', '')
            ->orderBy('id')
            ->eachById(function ($profile) {
                $pin = (string) $profile->adult_pin;
                $isHashed = str_starts_with($pin, '$2y$') || str_starts_with($pin, '$argon2');

                if (!$isHashed) {
                    DB::table('profiles')->where('id', $profile->id)->update([
                        'adult_pin' => Hash::make($pin),
                    ]);
                }
            });
    }

    public function down(): void
    {
        // Hashes não podem ser convertidos de volta para PINs em texto puro.
    }
};
