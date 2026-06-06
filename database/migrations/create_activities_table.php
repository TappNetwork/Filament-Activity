<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activities', function (Blueprint $table): void {
            $table->id();
            $table->nullableMorphs('actor');
            $table->morphs('subject');
            $table->nullableMorphs('parent');
            $table->string('event');
            $table->string('title')->nullable();
            $table->text('summary')->nullable();
            $table->json('metadata')->nullable();
            $table->foreignId('tenant_id')->nullable()->index();
            $table->timestamp('occurred_at')->index();
            $table->timestamps();

            $table->index(['tenant_id', 'occurred_at']);
            $table->index(['event', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
