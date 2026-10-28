<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $t) {
            if (!Schema::hasColumn('users', 'role')) {
                $t->string('role')->default('staff');
            }
            if (!Schema::hasColumn('users', 'is_active')) {
                $t->boolean('is_active')->default(false);
            }
        });

        // គណនីដែលមានស្រាប់ ឱ្យប្រើបាន
        DB::table('users')->update(['is_active' => true]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $t) {
            $t->dropColumn(['role', 'is_active']);
        });
    }
};
