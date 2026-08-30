<?php

use App\Models\HomeSection;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('home_sections', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('title');
            $table->index(['content_category_id', 'slug']);
        });

        HomeSection::query()->orderBy('id')->each(function (HomeSection $section) {
            $base = Str::slug($section->title) ?: "secao-{$section->id}";
            $slug = $base;
            $suffix = 2;

            while ($this->slugExistsInScope($slug, $section->content_category_id, $section->id)) {
                $slug = "{$base}-{$suffix}";
                $suffix++;
            }

            $section->forceFill(['slug' => $slug])->save();
        });
    }

    public function down(): void
    {
        Schema::table('home_sections', function (Blueprint $table) {
            $table->dropIndex(['content_category_id', 'slug']);
            $table->dropColumn('slug');
        });
    }

    private function slugExistsInScope(string $slug, ?int $categoryId, int $exceptId): bool
    {
        return HomeSection::query()
            ->where('slug', $slug)
            ->where('id', '!=', $exceptId)
            ->when(
                $categoryId === null,
                fn ($query) => $query->whereNull('content_category_id'),
                fn ($query) => $query->where('content_category_id', $categoryId),
            )
            ->exists();
    }
};
