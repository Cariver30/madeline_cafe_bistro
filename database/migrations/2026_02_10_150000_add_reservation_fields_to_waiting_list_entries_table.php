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
        Schema::table('waiting_list_entries', function (Blueprint $table) {
            if (! Schema::hasColumn('waiting_list_entries', 'reservation_at')) {
                $table->timestamp('reservation_at')->nullable()->after('quoted_at');
            }
            if (! Schema::hasColumn('waiting_list_entries', 'confirmation_received_at')) {
                $table->timestamp('confirmation_received_at')->nullable()->after('reservation_at');
            }
            if (! Schema::hasColumn('waiting_list_entries', 'reminder_30_sent_at')) {
                $table->timestamp('reminder_30_sent_at')->nullable()->after('confirmation_received_at');
            }
            if (! Schema::hasColumn('waiting_list_entries', 'reminder_10_sent_at')) {
                $table->timestamp('reminder_10_sent_at')->nullable()->after('reminder_30_sent_at');
            }
            if (! Schema::hasColumn('waiting_list_entries', 'auto_cancelled_at')) {
                $table->timestamp('auto_cancelled_at')->nullable()->after('reminder_10_sent_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('waiting_list_entries', function (Blueprint $table) {
            $columns = collect([
                'reservation_at',
                'confirmation_received_at',
                'reminder_30_sent_at',
                'reminder_10_sent_at',
                'auto_cancelled_at',
            ])->filter(fn ($column) => Schema::hasColumn('waiting_list_entries', $column))->all();

            if (! empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
