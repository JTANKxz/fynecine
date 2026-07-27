<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\PasswordResetCodeMail;
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

        // The same response prevents the endpoint from revealing which emails purchased.
        if ($purchase) {
            $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            DB::table('password_reset_codes')->updateOrInsert(
                ['email' => $email],
                ['code' => $code, 'expires_at' => now()->addMinutes(15), 'created_at' => now(), 'updated_at' => now()]
            );
            Mail::to($email)->send(new PasswordResetCodeMail($code, 'activation'));
        }

        return response()->json([
            'message' => 'Se existir uma compra aprovada para este e-mail, enviamos um código de confirmação.',
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
                $avatar = Avatar::where('is_default', true)->first();
                $user->profiles()->create(['name' => 'Perfil 1', 'is_main' => true, 'avatar' => $avatar?->image]);
                $this->applyPlan($user, $purchase);
                $purchase->update(['claimed_by_user_id' => $user->id, 'activated_at' => now()]);
                return $user;
            });
        } catch (\Throwable $exception) {
            return response()->json(['message' => $exception->getMessage() ?: 'Não foi possível ativar a conta.'], 422);
        }

        DB::table('password_reset_codes')->where('email', $email)->delete();
        return response()->json(['message' => 'Conta criada e plano ativado com sucesso.', 'email' => $user->email], 201);
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
        $campaignPlan = Str::after($purchase->status, ':');
        $planType = $campaignPlan === 'plus' ? 'premium' : 'basic';
        $plan = SubscriptionPlan::where('plan_type', $planType)->where('is_active', true)->first();
        if (!$plan) {
            abort(422, 'Plano da campanha não está disponível.');
        }

        $rank = ['free' => 0, 'basic' => 1, 'premium' => 2];
        $currentType = $user->plan_type ?: 'free';
        $keepCurrent = ($rank[$currentType] ?? 0) > ($rank[$planType] ?? 0) && $user->plan_expires_at?->isFuture();
        $start = $user->plan_expires_at && $user->plan_expires_at->isFuture() ? $user->plan_expires_at->copy() : now();
        if (!$keepCurrent) {
            $user->plan_type = $planType;
            $user->features = $plan->features ?? [];
        }
        $user->plan_expires_at = $start->addDays($plan->duration_days);
        $user->save();
    }
}
