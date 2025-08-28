<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });

        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('chat_groups')->cascadeOnDelete();
            $table->unsignedBigInteger('user_id');
            $table->text('content')->nullable();
            $table->string('type', 16); // text,image,pdf,voice
            $table->string('file_path')->nullable();
            $table->string('original_name')->nullable();
            $table->timestamps();
            $table->index(['group_id','id']);
        });

        Schema::create('chat_reactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_id')->constrained('chat_messages')->cascadeOnDelete();
            $table->unsignedBigInteger('user_id');
            $table->string('type', 32);
            $table->timestamps();
            $table->unique(['message_id','user_id']);
        });

        // Seed default groups
        $defaults = ['Bookings','Reports','Invoices','Management','Amendment Reports'];
        foreach ($defaults as $name) {
            DB::table('chat_groups')->insertOrIgnore([
                'name' => $name,
                'slug' => Str::slug($name),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_reactions');
        Schema::dropIfExists('chat_messages');
        Schema::dropIfExists('chat_groups');
    }
};
