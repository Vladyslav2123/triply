<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;

/**
 * @OA\PathItem(path="/")
 *
 * @OA\Tag(
 *     name="Authentication",
 *     description="API Endpoints для автентифікації та управління сесіями користувачів"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="sessionAuth",
 *     type="apiKey",
 *     in="header",
 *     name="X-XSRF-TOKEN",
 *     description="Додаток використовує сесійну автентифікацію з Laravel Sanctum (web guard) та X-XSRF-TOKEN для CSRF захисту. Клієнти повинні включати X-XSRF-TOKEN заголовок у всі запити, що змінюють дані."
 * )
 *
 * @OA\Post(
 *     path="/login",
 *     tags={"Authentication"},
 *     summary="Authenticate user and create session",
 *
 *     @OA\RequestBody(
 *         required=true,
 *
 *         @OA\JsonContent(
 *             required={"email","password"},
 *
 *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
 *             @OA\Property(property="password", type="string", format="password", example="password123"),
 *             @OA\Property(property="remember", type="boolean", example=false)
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Login successful",
 *
 *         @OA\JsonContent(
 *
 *             @OA\Property(property="two_factor", type="boolean", example=false),
 *             @OA\Property(property="status", type="string", example="success")
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=422,
 *         description="Validation error",
 *
 *         @OA\JsonContent(ref="#/components/schemas/ValidationError")
 *     ),
 *
 *     @OA\Response(
 *         response=429,
 *         description="Too Many Attempts",
 *
 *         @OA\JsonContent(ref="#/components/schemas/Error")
 *     )
 * )
 *
 * @OA\Post(
 *     path="/logout",
 *     tags={"Authentication"},
 *     summary="Logout user and invalidate session",
 *     security={{"sessionAuth":{}}},
 *
 *     @OA\Response(
 *         response=204,
 *         description="Logout successful"
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Unauthenticated",
 *
 *         @OA\JsonContent(ref="#/components/schemas/Error")
 *     )
 * )
 *
 * @OA\Post(
 *     path="/register",
 *     tags={"Authentication"},
 *     summary="Register a new user",
 *
 *     @OA\RequestBody(
 *         required=true,
 *
 *         @OA\JsonContent(
 *             required={"name","email","password","password_confirmation"},
 *
 *             @OA\Property(property="name", type="string", example="John Doe"),
 *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
 *             @OA\Property(property="password", type="string", format="password", example="password123"),
 *             @OA\Property(property="password_confirmation", type="string", example="password123")
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=201,
 *         description="Registration successful"
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation error",
 *
 *         @OA\JsonContent(ref="#/components/schemas/ValidationError")
 *     )
 * )
 *
 * @OA\Post(
 *     path="/forgot-password",
 *     tags={"Authentication"},
 *     summary="Send password reset link",
 *
 *     @OA\RequestBody(
 *         required=true,
 *
 *         @OA\JsonContent(
 *             required={"email"},
 *
 *             @OA\Property(property="email", type="string", format="email", example="user@example.com")
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Reset link sent successfully"
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation error",
 *
 *         @OA\JsonContent(ref="#/components/schemas/ValidationError")
 *     )
 * )
 *
 * @OA\Post(
 *     path="/reset-password",
 *     tags={"Authentication"},
 *     summary="Reset password",
 *
 *     @OA\RequestBody(
 *         required=true,
 *
 *         @OA\JsonContent(
 *             required={"token","email","password","password_confirmation"},
 *
 *             @OA\Property(property="token", type="string"),
 *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
 *             @OA\Property(property="password", type="string", format="password", example="newpassword123"),
 *             @OA\Property(property="password_confirmation", type="string", example="newpassword123")
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Password reset successful"
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation error",
 *
 *         @OA\JsonContent(ref="#/components/schemas/ValidationError")
 *     )
 * )
 *
 * @OA\Get(
 *     path="/user/profile",
 *     tags={"Profile"},
 *     summary="Get authenticated user profile",
 *     security={{"sessionAuth":{}}},
 *
 *     @OA\Response(
 *         response=200,
 *         description="User profile retrieved successfully",
 *
 *         @OA\JsonContent(ref="#/components/schemas/User")
 *     ),
 *
 *     @OA\Response(
 *         response=401,
 *         description="Unauthenticated",
 *
 *         @OA\JsonContent(ref="#/components/schemas/Error")
 *     )
 * )
 *
 * @OA\Put(
 *     path="/user/profile-information",
 *     tags={"Profile"},
 *     summary="Update user profile information",
 *     security={{"sessionAuth":{}}},
 *
 *     @OA\RequestBody(
 *         required=true,
 *
 *         @OA\JsonContent(
 *
 *             @OA\Property(property="name", type="string", example="John Doe"),
 *             @OA\Property(property="email", type="string", format="email", example="user@example.com")
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Profile updated successfully"
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation error",
 *
 *         @OA\JsonContent(ref="#/components/schemas/ValidationError")
 *     )
 * )
 *
 * @OA\Put(
 *     path="/user/password",
 *     tags={"Profile"},
 *     summary="Update user password",
 *     security={{"sessionAuth":{}}},
 *
 *     @OA\RequestBody(
 *         required=true,
 *
 *         @OA\JsonContent(
 *             required={"current_password","password","password_confirmation"},
 *
 *             @OA\Property(property="current_password", type="string", format="password"),
 *             @OA\Property(property="password", type="string", format="password"),
 *             @OA\Property(property="password_confirmation", type="string")
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Password updated successfully"
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation error",
 *
 *         @OA\JsonContent(ref="#/components/schemas/ValidationError")
 *     )
 * )
 *
 * @OA\Post(
 *     path="/email/verification-notification",
 *     tags={"Authentication"},
 *     summary="Resend email verification link",
 *     security={{"sessionAuth":{}}},
 *
 *     @OA\Response(
 *         response=200,
 *         description="Verification link sent"
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Unauthenticated",
 *
 *         @OA\JsonContent(ref="#/components/schemas/Error")
 *     )
 * )
 *
 * @OA\Get(
 *     path="/email/verify/{id}/{hash}",
 *     tags={"Authentication"},
 *     summary="Verify email address",
 *
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *
 *         @OA\Schema(type="integer")
 *     ),
 *
 *     @OA\Parameter(
 *         name="hash",
 *         in="path",
 *         required=true,
 *
 *         @OA\Schema(type="string")
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Email verified successfully"
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Invalid verification link",
 *
 *         @OA\JsonContent(ref="#/components/schemas/Error")
 *     )
 * )
 *
 * @OA\Post(
 *     path="/user/confirm-password",
 *     tags={"Authentication"},
 *     summary="Confirm user password",
 *     security={{"sessionAuth":{}}},
 *
 *     @OA\RequestBody(
 *         required=true,
 *
 *         @OA\JsonContent(
 *             required={"password"},
 *
 *             @OA\Property(property="password", type="string", format="password")
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Password confirmed"
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation error",
 *
 *         @OA\JsonContent(ref="#/components/schemas/ValidationError")
 *     )
 * )
 *
 * @OA\Post(
 *     path="/user/two-factor-authentication",
 *     tags={"Two Factor Authentication"},
 *     summary="Enable two factor authentication",
 *     security={{"sessionAuth":{}}},
 *
 *     @OA\Response(
 *         response=200,
 *         description="2FA enabled successfully"
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Unauthenticated",
 *
 *         @OA\JsonContent(ref="#/components/schemas/Error")
 *     )
 * )
 *
 * @OA\Delete(
 *     path="/user/two-factor-authentication",
 *     tags={"Two Factor Authentication"},
 *     summary="Disable two factor authentication",
 *     security={{"sessionAuth":{}}},
 *
 *     @OA\Response(
 *         response=200,
 *         description="2FA disabled successfully"
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Unauthenticated",
 *
 *         @OA\JsonContent(ref="#/components/schemas/Error")
 *     )
 * )
 *
 * @OA\Get(
 *     path="/user/two-factor-qr-code",
 *     tags={"Two Factor Authentication"},
 *     summary="Get 2FA QR code",
 *     security={{"sessionAuth":{}}},
 *
 *     @OA\Response(
 *         response=200,
 *         description="QR code retrieved successfully",
 *
 *         @OA\JsonContent(
 *
 *             @OA\Property(property="svg", type="string")
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=401,
 *         description="Unauthenticated",
 *
 *         @OA\JsonContent(ref="#/components/schemas/Error")
 *     )
 * )
 *
 * @OA\Get(
 *     path="/user/two-factor-recovery-codes",
 *     tags={"Two Factor Authentication"},
 *     summary="Get 2FA recovery codes",
 *     security={{"sessionAuth":{}}},
 *
 *     @OA\Response(
 *         response=200,
 *         description="Recovery codes retrieved successfully",
 *
 *         @OA\JsonContent(
 *             type="array",
 *
 *             @OA\Items(type="string")
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=401,
 *         description="Unauthenticated",
 *
 *         @OA\JsonContent(ref="#/components/schemas/Error")
 *     )
 * )
 *
 * @OA\Post(
 *     path="/user/two-factor-recovery-codes",
 *     tags={"Two Factor Authentication"},
 *     summary="Generate new 2FA recovery codes",
 *     security={{"sessionAuth":{}}},
 *
 *     @OA\Response(
 *         response=200,
 *         description="Recovery codes generated successfully",
 *
 *         @OA\JsonContent(
 *             type="array",
 *
 *             @OA\Items(type="string")
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=401,
 *         description="Unauthenticated",
 *
 *         @OA\JsonContent(ref="#/components/schemas/Error")
 *     )
 * )
 *
 * @OA\Post(
 *     path="/two-factor-challenge",
 *     tags={"Two Factor Authentication"},
 *     summary="Verify 2FA code",
 *
 *     @OA\RequestBody(
 *         required=true,
 *
 *         @OA\JsonContent(
 *             required={"code"},
 *
 *             @OA\Property(property="code", type="string", example="123456")
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="2FA verification successful"
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Invalid code",
 *
 *         @OA\JsonContent(ref="#/components/schemas/ValidationError")
 *     )
 * )
 */
abstract class Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
}
