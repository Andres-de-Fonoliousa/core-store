<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class TelegramLinkController extends Controller
{
    public function sendOtp(Request $request): JsonResponse
    {
        $request->validate(['email' => 'required|email|exists:users,email']);

        $email = $request->email;

        $existing = Cache::get("telegram_otp_{$email}");
        if ($existing && (time() - $existing['created_at']) < 60) {
            return response()->json(['error' => 'Please wait before requesting a new code'], 429);
        }

        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        Cache::put("telegram_otp_{$email}", [
            'otp' => $otp,
            'created_at' => time(),
        ], now()->addMinutes(5));

        Mail::raw(
            "Your Telegram link code is: {$otp}\n\nThis code expires in 5 minutes.",
            fn ($msg) => $msg->to($email)->subject('Telegram Account Link Code')
        );

        return response()->json(['message' => 'OTP sent to your email']);
    }

    public function verifyOtp(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email|exists:users,email',
            'otp' => 'required|string|size:6',
            'telegram_id' => 'required|string|max:50',
            'telegram_username' => 'nullable|string|max:255',
        ]);

        $cached = Cache::get("telegram_otp_{$validated['email']}");

        if (!$cached || $cached['otp'] !== $validated['otp']) {
            return response()->json(['error' => 'Invalid or expired OTP'], 422);
        }

        $user = User::where('email', $validated['email'])->first();

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        if ($user->telegram_id && $user->telegram_id !== $validated['telegram_id']) {
            return response()->json(['error' => 'Email already linked to another Telegram account'], 422);
        }

        $user->update([
            'telegram_id' => $validated['telegram_id'],
            'telegram_username' => $validated['telegram_username'],
        ]);

        Cache::forget("telegram_otp_{$validated['email']}");

        $token = $user->createToken('telegram-bot', ['*'])->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'balance' => (float) $user->balance,
            ],
        ]);
    }
}
