<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->uuid('request_id');
            $table->foreignIdFor(\App\Models\User::class);
            $table->string('contact');            // значение sms, email, возможно telegram в дальнейшем итд
            $table->string('channel');            // sms, email, возможно telegram в дальнейшем итд
            $table->text('message');              // Текст сообщения
            $table->string('priority');           // high или low
            $table->string('status')->default('queued'); // Статусы: queued, sent, delivered, canceled
            $table->text('error_details')->nullable(); //  Данные ошибки
            $table->integer('retry_count')->default(0);

            $table->unique(['request_id', 'user_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
