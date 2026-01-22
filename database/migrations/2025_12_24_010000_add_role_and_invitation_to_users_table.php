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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'role')) {
                $table->string('role')->default('admin')->after('remember_token');
            }
            if (!Schema::hasColumn('users', 'invitation_token')) {
                $table->string('invitation_token')->nullable()->after('role');
            }
            if (!Schema::hasColumn('users', 'invitation_sent_at')) {
                $table->timestamp('invitation_sent_at')->nullable()->after('invitation_token');
            }
            if (!Schema::hasColumn('users', 'invitation_accepted_at')) {
                $table->timestamp('invitation_accepted_at')->nullable()->after('invitation_sent_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = collect([
                'role',
                'invitation_token',
                'invitation_sent_at',
                'invitation_accepted_at',
            ])->filter(fn ($column) => Schema::hasColumn('users', $column))->all();

            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
