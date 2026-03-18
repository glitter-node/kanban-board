<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cards', function (Blueprint $table) {
            if (! Schema::hasColumn('cards', 'blocked')) {
                $table->boolean('blocked')->default(false)->after('status');
            }

            if (! Schema::hasColumn('cards', 'blocked_reason')) {
                $table->text('blocked_reason')->nullable()->after('blocked');
            }

            if (! Schema::hasColumn('cards', 'moved_to_done_at')) {
                $table->timestamp('moved_to_done_at')->nullable()->after('completed_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('cards', function (Blueprint $table) {
            if (Schema::hasColumn('cards', 'moved_to_done_at')) {
                $table->dropColumn('moved_to_done_at');
            }

            if (Schema::hasColumn('cards', 'blocked_reason')) {
                $table->dropColumn('blocked_reason');
            }

            if (Schema::hasColumn('cards', 'blocked')) {
                $table->dropColumn('blocked');
            }
        });
    }
};
