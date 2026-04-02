<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('event_id')->nullable()->after('vendor_profile_id')->constrained('events')->nullOnDelete();
            $table->string('currency', 3)->default('USD')->after('total');
            $table->string('payment_provider')->nullable()->after('status')->index();
            $table->string('payment_intent_id')->nullable()->after('payment_provider')->index();
            $table->string('checkout_session_id')->nullable()->after('payment_intent_id')->index();
            $table->foreignId('referred_by_user_id')->nullable()->after('checkout_session_id')->constrained('users')->nullOnDelete();
            $table->string('referral_source')->nullable()->after('referred_by_user_id');
            $table->timestamp('cancelled_at')->nullable()->after('paid_at');
            $table->text('failed_reason')->nullable()->after('cancelled_at');
        });

        DB::table('orders')->orderBy('id')->chunkById(100, function ($orders) {
            foreach ($orders as $order) {
                $eventId = DB::table('order_items')
                    ->join('ticket_types', 'ticket_types.id', '=', 'order_items.ticket_type_id')
                    ->where('order_items.order_id', $order->id)
                    ->value('ticket_types.event_id');

                if ($eventId) {
                    DB::table('orders')->where('id', $order->id)->update(['event_id' => $eventId]);
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('event_id');
            $table->dropConstrainedForeignId('referred_by_user_id');
            $table->dropColumn([
                'currency',
                'payment_provider',
                'payment_intent_id',
                'checkout_session_id',
                'referral_source',
                'cancelled_at',
                'failed_reason',
            ]);
        });
    }
};
