<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function registerIndividual(Request $request): JsonResponse
    {
        $this->normalizeAuthInput($request);

        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:120'],
            'last_name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:32', 'unique:users,phone'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = User::query()->create([
            'type' => User::TYPE_INDIVIDUAL,
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'name' => trim($data['first_name'].' '.$data['last_name']),
            'email' => $data['email'],
            'phone' => $data['phone'],
            'password' => $data['password'],
            'is_active' => true,
        ]);

        return $this->tokenResponse($user, 201);
    }

    public function registerEnterprise(Request $request): JsonResponse
    {
        $this->normalizeAuthInput($request);

        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:120'],
            'last_name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:32', 'unique:users,phone'],
            'company_name' => ['required', 'string', 'max:190'],
            'expected_volume' => ['required', 'string', Rule::in(['1-50', '51-100', '100-200', '200+'])],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = User::query()->create([
            'type' => User::TYPE_ENTERPRISE,
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'name' => trim($data['first_name'].' '.$data['last_name']),
            'email' => $data['email'],
            'phone' => $data['phone'],
            'company_name' => $data['company_name'],
            'expected_volume' => $data['expected_volume'],
            'password' => $data['password'],
            'is_active' => true,
        ]);

        return $this->tokenResponse($user, 201);
    }

    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! $token = auth('api')->attempt($credentials)) {
            return response()->json(['message' => __('general.auth_invalid_credentials')], 401);
        }

        /** @var User $user */
        $user = auth('api')->user();
        if (! $user->is_active) {
            auth('api')->logout();

            return response()->json(['message' => __('general.auth_account_inactive')], 403);
        }

        return $this->tokenResponse($user, 200, $token);
    }

    public function me(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user('api');

        return response()->json(['data' => $this->userPayload($user)]);
    }

    public function updateMe(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user('api');

        $this->normalizeAuthInput($request);

        $data = $request->validate([
            'first_name' => ['sometimes', 'string', 'max:120'],
            'last_name' => ['sometimes', 'string', 'max:120'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:32', Rule::unique('users', 'phone')->ignore($user->id)],
            'profile_image' => ['sometimes', 'nullable', 'image', 'max:4096'],
            'company_name' => ['sometimes', 'nullable', 'string', 'max:190'],
            'expected_volume' => ['sometimes', 'nullable', 'string', Rule::in(['1-50', '51-100', '100-200', '200+'])],
            'password' => ['sometimes', 'confirmed', Password::defaults()],
        ]);

        if ($request->hasFile('profile_image')) {
            $filename = 'user-'.$user->id.'-'.time().'.'.$request->file('profile_image')->getClientOriginalExtension();
            $request->file('profile_image')->move(public_path(uploadsDir('front')), $filename);
            $data['profile_image'] = $filename;
        }

        if (isset($data['first_name']) || isset($data['last_name'])) {
            $first = $data['first_name'] ?? $user->first_name;
            $last = $data['last_name'] ?? $user->last_name;
            $data['name'] = trim(($first ?? '').' '.($last ?? ''));
        }

        $user->fill($data)->save();

        return response()->json(['data' => $this->userPayload($user->fresh())]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function userPayload(User $user): array
    {
        $payload = [
            'id' => $user->id,
            'type' => $user->type,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'name' => $user->fullName(),
            'email' => $user->email,
            'phone' => $user->phone,
            'profile_image' => $user->profile_image
                ? asset(uploadsDir('front').$user->profile_image)
                : null,
        ];

        if ($user->isEnterprise()) {
            $payload['company_name'] = $user->company_name;
            $payload['expected_volume'] = $user->expected_volume;
        }

        return $payload;
    }

    protected function tokenResponse(User $user, int $status = 200, ?string $token = null): JsonResponse
    {
        $token ??= auth('api')->login($user);

        return response()->json([
            'data' => [
                'token' => $token,
                'token_type' => 'bearer',
                'expires_in' => auth('api')->factory()->getTTL() * 60,
                'user' => $this->userPayload($user),
            ],
        ], $status);
    }

    /**
     * Accept camelCase aliases and normalize expected_volume dashes.
     */
    protected function normalizeAuthInput(Request $request): void
    {
        $aliases = [
            'firstName' => 'first_name',
            'lastName' => 'last_name',
            'companyName' => 'company_name',
            'expectedVolume' => 'expected_volume',
            'passwordConfirmation' => 'password_confirmation',
            'profileImage' => 'profile_image',
        ];

        $merge = [];

        foreach ($aliases as $from => $to) {
            if (! $request->filled($to) && $request->filled($from)) {
                $merge[$to] = $request->input($from);
            }
        }

        $volume = $merge['expected_volume'] ?? $request->input('expected_volume');
        if (is_string($volume) && $volume !== '') {
            $merge['expected_volume'] = str_replace(
                ["\u{2013}", "\u{2014}", '–', '—'],
                '-',
                trim($volume)
            );
        }

        if ($merge !== []) {
            $request->merge($merge);
        }
    }
}
