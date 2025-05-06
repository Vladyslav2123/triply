<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Fortify\CreateNewUser;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginOtpRequest;
use App\Http\Requests\Auth\RegisterOtpRequest;
use App\Http\Requests\Auth\SendOtpRequest;
use App\Models\User;
use App\Notifications\PasswordCreateNotification;
use App\Services\OTPService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class RegisterController extends Controller
{
    private const MESSAGE_OTP_SENT = 'OTP sent successfully';

    private const MESSAGE_INVALID_OTP = 'Invalid OTP';

    private const MESSAGE_LOGIN_SUCCESS = 'Login successful';

    private const MESSAGE_REGISTER_SUCCESS = 'Registered successfully';

    private const MESSAGE_FAILED_SEND_OTP = 'Failed to send OTP';

    private const MESSAGE_FAILED_LOGIN = 'Login failed';

    private const MESSAGE_FAILED_REGISTER = 'Registration failed';

    public function __construct(
        private readonly OTPService $otpService,
        private readonly CreateNewUser $createNewUser
    ) {}

    /**
     * @OA\Post(
     *     path="/api/v1/send-otp",
     *     summary="Send OTP code to phone number",
     *     tags={"Authentication"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"phone"},
     *
     *             @OA\Property(
     *                 property="phone",
     *                 type="string",
     *                 example="+380991234567",
     *                 description="Phone number in international format"
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="OTP sent successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="OTP sent successfully")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Failed to send OTP",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="Failed to send OTP")
     *         )
     *     )
     * )
     */
    public function sendOtp(SendOtpRequest $request): JsonResponse
    {
        try {
            $this->otpService::generateAndSendOtp($request->validated('phone'));

            return response()->json([
                'message' => self::MESSAGE_OTP_SENT,
            ]);
        } catch (Exception $e) {
            Log::error('Failed to send OTP', [
                'phone' => $request->phone,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => self::MESSAGE_FAILED_SEND_OTP,
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/auth/register-otp",
     *     summary="Register new user using OTP",
     *     tags={"Authentication"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"phone", "otp", "name", "email"},
     *
     *             @OA\Property(property="phone", type="string", example="+380991234567"),
     *             @OA\Property(property="otp", type="string", example="123456"),
     *             @OA\Property(property="name", type="string", example="John"),
     *             @OA\Property(property="surname", type="string", nullable=true, example="Doe"),
     *             @OA\Property(property="birth_date", type="string", format="date",nullable=true, example="1990-01-01"),
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *             @OA\Property(property="photo", type="string", format="binary", nullable=true),
     *             @OA\Property(property="remember", type="boolean",nullable=true, example=false)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="User registered successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="Registered successfully"),
     *             @OA\Property(
     *                 property="user",
     *                 ref="#/components/schemas/User"
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Invalid OTP",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="Invalid OTP")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Registration failed",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="Registration failed")
     *         )
     *     )
     * )
     */
    public function registerOtp(RegisterOtpRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            if (! $this->otpService::verifyOTP($validated['phone'], $validated['otp'])) {
                return response()->json([
                    'message' => self::MESSAGE_INVALID_OTP,
                ], 401);
            }

            $plainPassword = Str::random(12);

            $userData = [
                ...$validated,
                'password' => $plainPassword,
                'photo' => $request->hasFile('photo') ? $request->file('photo') : null,
            ];

            $user = $this->createNewUser->create($userData);
            $profile = $user->getOrCreateProfile();

            $user->notify(new PasswordCreateNotification(
                name: $user->full_name,
                password: $plainPassword
            ));

            Auth::guard('web')->login($user, $request->boolean('remember', false));

            return response()->json([
                'message' => self::MESSAGE_REGISTER_SUCCESS,
                'user' => $user->load('profile'),
            ]);

        } catch (Exception $e) {
            Log::error('Registration failed', [
                'data' => $request->validated(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => self::MESSAGE_FAILED_REGISTER,
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/auth/login-otp",
     *     summary="Login using OTP",
     *     tags={"Authentication"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"phone", "otp"},
     *
     *             @OA\Property(
     *                 property="phone",
     *                 type="string",
     *                 example="+380991234567",
     *                 description="Phone number in international format"
     *             ),
     *             @OA\Property(
     *                 property="otp",
     *                 type="string",
     *                 example="123456",
     *                 description="6-digit OTP code"
     *             ),
     *             @OA\Property(
     *                 property="remember",
     *                 type="boolean",
     *                 nullable=true,
     *                 example=false,
     *                 description="Remember user session"
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Login successful",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="Login successful"),
     *             @OA\Property(
     *                 property="user",
     *                 ref="#/components/schemas/User"
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Invalid credentials",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="Invalid OTP")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Login failed",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="Login failed")
     *         )
     *     )
     * )
     */
    public function loginOtp(LoginOtpRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            $user = User::where('phone', $validated['phone'])->first();

            if (! $user || ! $this->otpService::verifyOTP($validated['phone'], $validated['otp'])) {
                return response()->json([
                    'message' => self::MESSAGE_INVALID_OTP,
                ], 401);
            }

            Auth::guard('web')->login($user, $request->boolean('remember', false));

            return response()->json([
                'message' => self::MESSAGE_LOGIN_SUCCESS,
                'user' => $user,
            ]);

        } catch (Exception $e) {
            Log::error('Login failed', [
                'phone' => $request->phone,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => self::MESSAGE_FAILED_LOGIN,
            ], 500);
        }
    }
}
