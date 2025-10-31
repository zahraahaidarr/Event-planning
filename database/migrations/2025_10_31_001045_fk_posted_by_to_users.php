<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Drop current FK on posted_by (whatever its name is)
        if ($fk = DB::selectOne("
            SELECT CONSTRAINT_NAME name
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME   = 'announcements'
              AND COLUMN_NAME  = 'posted_by'
        ")) {
            DB::statement("ALTER TABLE `announcements` DROP FOREIGN KEY `{$fk->name}`");
        }

        // Make posted_by the same type as users.id
        // If your users.id is INT, change to unsignedInteger()
        Schema::table('announcements', function (Blueprint $t) {
            $t->unsignedBigInteger('posted_by')->nullable()->change();
        });

        // If some rows currently store employees.employee_id, map them to users.id via employees.user_id
        DB::statement("
            UPDATE announcements a
            JOIN employees e ON e.employee_id = a.posted_by
            SET a.posted_by = e.user_id
            WHERE a.posted_by IS NOT NULL
        ");

        // Recreate FK to users(id)
        DB::statement("
            ALTER TABLE `announcements`
            ADD CONSTRAINT `announcements_posted_by_foreign`
            FOREIGN KEY (`posted_by`) REFERENCES `users`(`id`)
            ON DELETE SET NULL
        ");
    }

    public function down(): void
    {
        // (Optional) reverse back to employees(...) if you ever need it
        if ($fk = DB::selectOne("
            SELECT CONSTRAINT_NAME name
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME   = 'announcements'
              AND COLUMN_NAME  = 'posted_by'
        ")) {
            DB::statement("ALTER TABLE `announcements` DROP FOREIGN KEY `{$fk->name}`");
        }

        Schema::table('announcements', function (Blueprint $t) {
            $t->unsignedInteger('posted_by')->nullable()->change();
        });

        DB::statement("
            ALTER TABLE `announcements`
            ADD CONSTRAINT `announcements_posted_by_foreign`
            FOREIGN KEY (`posted_by`) REFERENCES `employees`(`employee_id`)
            ON DELETE SET NULL
        ");
    }
};
