<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\Shared\Enums\DatabaseIdentifier;

return new class extends Migration
{
    /**
     * @inheritdoc
     */
    protected $connection = DatabaseIdentifier::HISTORY->value;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('histories', static function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')
                ->unique()
                ->index();
            $table->boolean('is_scanned');
            $table->string('type')->index();
            $table->unsignedBigInteger('user_id')->index();
            $table->json('metadata')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('histories');
    }
};
