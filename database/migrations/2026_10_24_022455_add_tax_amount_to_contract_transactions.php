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
        Schema::table('contract_transactions', function (Blueprint $t) {
            $t->decimal('tax_amount', 12, 2)->default(0)->after('amount');
        });
    }
    public function down(): void
    {
        Schema::table('contract_transactions', fn(Blueprint $t) => $t->dropColumn('tax_amount'));
    }
};
