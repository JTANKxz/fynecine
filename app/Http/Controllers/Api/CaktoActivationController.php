<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\PasswordResetCodeMail;
use App\Models\AppConfig;
use App\Models\Avatar;
use App\Models\CaktoCampaignPurchase;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CaktoActivationController extends Controller
{
    public function start(Request $request): JsonResponse
    {
        $validated = $request->validate(['email' => ['required', 'email', 'max:255']]);
        $email = strtolower($validated['email']);
        $purchase = $this->pendingPurchase($email);

        if (!$purchase) {
            return response()->json([
                'message' => 'Não encontramos uma compra aprovada pendente para este e-mail. Use o mesmo e-mail informado no checkout.',
            ], 404);
        }

        try {
            $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            DB::table('password_reset_codes')->updateOrInsert(
                ['email' => $email],
                ['code' => $code, 'expires_at' => now()->addMinutes(15), 'created_at' => now(), 'updated_at' => now()]
            );
            Mail::to($email)->send(new PasswordResetCodeMail($code, 'activation'));
        } catch (\Throwable $exception) {
            \Log::error('Não foi possível enviar o código de ativação Cakto.', [
                'purchase_id' => $purchase->id,
                'error' => $exception->getMessage(),
            ]);

            return response()->json([
                'message' => 'Não foi possível enviar o código agora. Tente novamente em alguns minutos.',
            ], 503);
        }

        return response()->json([
            'message' => 'Enviamos um código de confirmação para o e-mail usado na compra.',
        ]);
    }

    public function verify(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'code' => ['required', 'string', 'size:6'],
        ]);
        $email = strtolower($validated['email']);
        $purchase = $this->pendingPurchase($email);
        if (!$purchase || !$this->validCode($email, $validated['code'])) {
            return response()->json(['message' => 'Código inválido, expirado ou compra não disponível.'], 422);
        }

        $user = User::whereRaw('LOWER(email) = ?', [$email])->first();
        if ($user) {
            DB::transaction(function () use ($purchase, $user) {
                $locked = CaktoCampaignPurchase::lockForUpdate()->findOrFail($purchase->id);
                if ($locked->claimed_by_user_id) {
                    return;
                }
                $this->applyPlan($user, $locked);
                $locked->update(['claimed_by_user_id' => $user->id, 'activated_at' => now()]);
            });
            DB::table('password_reset_codes')->where('email', $email)->delete();
            return response()->json(['account_exists' => true, 'message' => 'Compra ativada na sua conta existente. Faça login para continuar.']);
        }

        return response()->json([
            'account_exists' => false,
            'name' => $purchase->buyer_name ?: '',
            'email' => $email,
            'username' => $this->suggestUsername($purchase->buyer_name ?: Str::before($email, '@')),
        ]);
    }

    public function confirm(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'code' => ['required', 'string', 'size:6'],
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'min:3', 'max:255', 'regex:/^[a-z0-9._-]+$/', Rule::unique('users', 'username')],
            'avatar' => ['nullable', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);
        $email = strtolower($validated['email']);
        if (!$this->validCode($email, $validated['code'])) {
            return response()->json(['message' => 'Código inválido ou expirado.'], 422);
        }

        try {
            $user = DB::transaction(function () use ($validated, $email) {
                $purchase = $this->pendingPurchase($email, true);
                if (!$purchase) {
                    abort(422, 'Esta compra já foi ativada ou não está disponível.');
                }
                if (User::whereRaw('LOWER(email) = ?', [$email])->exists()) {
                    abort(422, 'Já existe uma conta para este e-mail.');
                }

                $user = User::create([
                    'name' => $validated['name'],
                    'username' => strtolower($validated['username']),
                    'email' => $email,
                    'password' => Hash::make($validated['password']),
                ]);
                // The panel default is used when the customer does not choose an avatar.
                $config = AppConfig::getSettings();
                $avatar = Avatar::find($config->default_avatar_p1)
                    ?? Avatar::where('is_default', true)->first();
                $selectedAvatar = null;
                if (!empty($validated['avatar'])) {
                    $selectedAvatar = Avatar::query()->get()->first(function (Avatar $candidate) use ($validated) {
                        return $candidate->image === $validated['avatar'] || $candidate->image_url === $validated['avatar'];
                    });
                }
                $user->profiles()->create([
                    'name' => 'Perfil 1',
                    'is_main' => true,
                    'avatar' => ($selectedAvatar ?? $avatar)?->image,
                ]);
                $this->applyPlan($user, $purchase);
                $purchase->update(['claimed_by_user_id' => $user->id, 'activated_at' => now()]);
                return $user;
            });
        } catch (\Throwable $exception) {
            return response()->json(['message' => $exception->getMessage() ?: 'Não foi possível ativar a conta.'], 422);
        }

        DB::table('password_reset_codes')->where('email', $email)->delete();
        $token = $user->createToken('campaign-activation')->plainTextToken;

        return response()->json([
            'message' => 'Conta criada e plano ativado com sucesso.',
            'email' => $user->email,
            'user' => $user->fresh(),
            'token' => $token,
        ], 201);
    }

    private function pendingPurchase(string $email, bool $lock = false): ?CaktoCampaignPurchase
    {
        $query = CaktoCampaignPurchase::query()
            ->whereRaw('LOWER(buyer_email) = ?', [strtolower($email)])
            ->whereNull('claimed_by_user_id')
            ->whereIn('status', ['approved:basic', 'approved:plus'])
            ->latest('approved_at');
        if ($lock) {
            $query->lockForUpdate();
        }
        return $query->first();
    }

    private function validCode(string $email, string $code): bool
    {
        return DB::table('password_reset_codes')->where('email', $email)->where('code', $code)->where('expires_at', '>', now())->exists();
    }

    private function suggestUsername(string $name): string
    {
        $base = Str::of(Str::ascii($name))->lower()->replaceMatches('/[^a-z0-9]+/', '.')->trim('.')->value() ?: 'usuario';
        $candidate = $base;
        while (User::where('username', $candidate)->exists()) {
            $candidate = $base . random_int(100, 999);
        }
        return $candidate;
    }

    private function applyPlan(User $user, CaktoCampaignPurchase $purchase): void
    {
        // Campaign checkout products are only acquisition channels. Every
        // approved campaign purchase grants the same Premium monthly access.
        $plan = SubscriptionPlan::query()
            ->where('plan_type', 'premium')
            ->where('is_active', true)
            ->where('duration_days', 30)
            ->first();

        if (!$plan) {
            abort(422, 'O plano Premium mensal da campanha nao esta disponivel.');
        }

        $currentType = $user->plan_type ?: 'free';
        $currentExpiry = $user->plan_expires_at;
        $start = $currentExpiry && $currentExpiry->isFuture() ? $currentExpiry->copy() : now();

        // Preserve an existing Premium entitlement, but always extend access
        // by one month. New/free/basic users receive Premium features.
        if ($currentType !== 'premium' || !$currentExpiry?->isFuture()) {
            $user->plan_type = 'premium';
            $user->features = $plan->features ?? [];
        }

        $user->plan_expires_at = $start->addDays(30);
        $user->save();
    }
}
