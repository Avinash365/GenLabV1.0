<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Attempt to clean up constraints manually
        try { DB::statement("ALTER TABLE zoom_meeting_attendees DROP FOREIGN KEY zoom_meeting_attendees_zoom_meeting_id_foreign"); } catch(\Exception $e){}
        try { DB::statement("ALTER TABLE zoom_meeting_attendees DROP FOREIGN KEY zoom_meeting_attendees_user_id_foreign"); } catch(\Exception $e){}
        try { DB::statement("ALTER TABLE zoom_meeting_attendees DROP INDEX zoom_meeting_attendees_zoom_meeting_id_user_id_unique"); } catch(\Exception $e){}

        Schema::table('zoom_meeting_attendees', function (Blueprint $table) {
            
            // Add new columns
            if (!Schema::hasColumn('zoom_meeting_attendees', 'user_type')) {
                $table->string('user_type')->nullable()->after('user_id');
            }
            if (!Schema::hasColumn('zoom_meeting_attendees', 'attendee_name')) {
                $table->string('attendee_name')->nullable()->after('user_type');
            }

            // Re-add zoom_meeting_id foreign key
            // Note: We check if index exists? No, just add it. Laravel handles naming.
            // But if we dropped it manually, Laravel might try to create it with same name.
            $table->foreign('zoom_meeting_id')
                  ->references('id')
                  ->on('zoom_meetings')
                  ->onDelete('cascade');

            // Add new unique index
            $table->unique(['zoom_meeting_id', 'user_id', 'user_type'], 'zm_attendees_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
       // Irrelevant for now
    }
};

