<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_document_versions', function (Blueprint $table) {
            $table->string('approval_status', 32)->default('pending')->after('notes');
            $table->text('revision_notes')->nullable()->after('approval_status');
            $table->timestamp('reviewed_at')->nullable()->after('revision_notes');
            $table->foreignId('reviewed_by')->nullable()->after('reviewed_at')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('project_document_versions', function (Blueprint $table) {
            $table->dropForeign(['reviewed_by']);
            $table->dropColumn(['approval_status', 'revision_notes', 'reviewed_at', 'reviewed_by']);
        });
    }
};
