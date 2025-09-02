<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SendOrderMessege implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $phone,           
        public string $customerName,   
        public ?string $orderNumber = null
    )
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $cfg = config('services.bulksmsbd');

        $to  = $this->normalizeBangladeshMsisdn($this->phone); // -> "8801XXXXXXXXX"
        $msg = $this->buildMessage();

        $payload = [
            'api_key'  => $cfg['api_key'],
            'senderid' => $cfg['sender_id'],
            'number'   => $to,
            'message'  => $msg,
        ];

        $resp = Http::asForm()->timeout(15)->post($cfg['endpoint'], $payload);

        if (!$resp->successful()) {
            Log::error('[SMS] HTTP error', ['status' => $resp->status(), 'body' => $resp->body()]);
            throw new \RuntimeException('BulkSMSBD HTTP error: '.$resp->status());
        }

        // BulkSMSBD returns codes like 202 (submitted) etc. Keep a light sanity check:
        $body = (string)$resp->body();
        Log::info('[SMS] BulkSMSBD response', ['body' => $body]);
    }

        private function buildMessage(): string
    {
        $ref = $this->orderNumber ? " (Order: {$this->orderNumber})" : '';
        // Keep it < 160 chars if you want single-segment English
        return "Ponnobd Electronics\nপ্রিয় গ্রাহক!\nআপনার {$ref}\nঅর্ডারটি সফলভাবে গ্রহণ করা হয়েছে।\nবিস্তারিত: +8801880162323";

    }

    private function normalizeBangladeshMsisdn(string $raw): string
    {
        $s = preg_replace('/\D+/', '', $raw ?? '');
        if (Str::startsWith($s, '8801') && strlen($s) === 13) return $s;
        if (Str::startsWith($s, '01')  && strlen($s) === 11) return '88'.$s;
        if (Str::startsWith($s, '1')   && strlen($s) === 10) return '880'.$s; // e.g. 1XXXXXXXXX
        if (Str::startsWith($s, '+8801')) return ltrim($s, '+');
        return $s; // last resort – send as-is
    }
}
