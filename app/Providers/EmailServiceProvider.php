<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Mail\AdminNewOrderMail;
use App\Models\Order;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Jobs\SendOrderMessege;

class EmailServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Log::info('[OrderMailSP] boot()');

        Order::created(function (Order $order) {
            Log::info('[OrderMailSP] Order::created', ['order_details' => $order]);
           
            $billing  = is_array($order->billing)  ? $order->billing  : json_decode($order->billing ?? '{}', true);
            

            DB::afterCommit(function () use ($order, $billing) {
                Log::info('[OrderMailSP] afterCommit firing', ['order_id' => $order->code]);

                try {
                    // Use send() while debugging so queues don’t hide issues
                    Mail::to(config('mail.admin_address'))->queue(new AdminNewOrderMail($order));
                    
                    // 2) Queue customer thank-you SMS
                    SendOrderMessege::dispatch(
                        phone: $billing['phone'],
                        customerName: $billing['full_name'] ?? 'Customer',
                        orderNumber: $order->code ?? (string) $order->code
                    )->onQueue('sms');

                    Log::info('[OrderMailSP] Queued mail + SMS', ['order_id' => $order->code]);
                    
                } catch (\Throwable $e) {
                    Log::error('[OrderMailSP] Mail error', ['order_id' => $order->code, 'msg' => $e->getMessage()]);
                }
            });
        });
    }
}
