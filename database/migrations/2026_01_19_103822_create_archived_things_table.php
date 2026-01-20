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
        Schema::create('archived_things', function (Blueprint $table) {
            $table->id();
            $table->string('thing_name')->comment('Название удаленной вещи');
            $table->text('current_description')->nullable()->comment('Актуальное описание вещи на момент удаления');
            $table->string('master_name')->comment('Имя хозяина вещи');
            $table->string('last_user_name')->nullable()->comment('Имя последнего пользователя вещи');
            $table->string('place_name')->nullable()->comment('Название места хранения');
            $table->boolean('is_restored')->default(false)->comment('Флаг восстановления вещи');
            $table->foreignId('restored_by')->nullable()->constrained('users')->onDelete('set null')->comment('Пользователь, восстановивший вещь');
            $table->timestamp('restored_at')->nullable()->comment('Дата и время восстановления');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('archived_things');
    }
};
