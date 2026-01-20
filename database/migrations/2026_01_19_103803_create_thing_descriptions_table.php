<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('thing_descriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('thing_id')->constrained('things')->onDelete('cascade')->comment('Идентификатор вещи');
            $table->text('description')->comment('Текст описания вещи');
            $table->boolean('is_current')->default(false)->comment('Флаг актуального описания для отображения в списке');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null')->comment('Пользователь, создавший описание');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('thing_descriptions');
    }
};
