<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Migration intentionally left empty – foreign key already added in the table creation migration.
    }

    public function down(): void
    {
        // No actions needed for rollback.
    }
};
?>
