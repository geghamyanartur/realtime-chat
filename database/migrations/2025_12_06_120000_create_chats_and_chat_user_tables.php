<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('chats', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('is_private')->default(false);
            $table->string('invite_code')->nullable()->unique();
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('chat_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['chat_id', 'user_id']);
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->foreignId('chat_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
        });

        $publicChatId = DB::table('chats')->insertGetId([
            'name' => 'General',
            'is_private' => false,
            'invite_code' => null,
            'owner_id' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('messages')->update(['chat_id' => $publicChatId]);
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropConstrainedForeignId('chat_id');
        });

        Schema::dropIfExists('chat_user');
        Schema::dropIfExists('chats');
    }
};
