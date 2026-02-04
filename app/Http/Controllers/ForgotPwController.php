<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;
use Mpdf\Tag\U;
use App\Models\User;

class ForgotPwController extends Controller
{
    // public function index()
    // {
    //     $forgot = "Forgot Password";
    //     return view('forgot_pw', ['forgot' => $forgot]);
    // }

    public function showForgotPasswordForm()
    {
        //return view('auth.forgot-password');
        return view('auth.forgot-password');
    }

    public function sendVerificationEmail(Request $request)
    {

        $request->validate(
            [
                'email' => 'required|email|exists:users,email',
                'nip' => 'required|string|exists:users,nip',
            ],
            [
                'email.exists' => 'User dengan email ' . $request->email . ' tidak ditemukan.',
                'nip.exists' => 'User dengan NIP ' . $request->nip . ' tidak ditemukan.',
            ]
        );
        if (User::where('email', $request->email)->where('nip', $request->nip)->doesntExist()) {
            return back()->withErrors(['email' => 'Email dan NIP tidak sesuai.']);
        }
        $status = Password::sendResetLink(
            $request->only('email')
        );
        //dd($request->all(), $status);
        return $status === Password::ResetLinkSent
            ? back()->with(['status' => __($status)])
            : back()->withErrors(['email' => __($status)]);
    }

    public function updatePassword(Request $request)
    {

        $request->validate([
            'token' => 'required',
            // 'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        // $record = DB::table('password_reset_tokens')->where('token', $request->token)->first();
        // if (!$record) {
        //     return back()->withErrors(['email' => 'Terjadi kesalahan, harap ulangi permintaan reset password.']);
        // }

        // $user = User::where('email', $record->email)->first();
        // if (!$user) {
        //     return back()->withErrors(['email' => 'Email salah.']);
        // }
        // $user->update([
        //     'password' => Hash::make($request->password)
        // ]);

        // // Delete token
        // DB::table('password_reset_tokens')->where('email', $record->email)->delete();

        // return redirect()->route('login')->with('success', 'Password berhasil direset.');
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        return $status === Password::PasswordReset
            ? redirect()->route('login')->with('status', __($status))
            : back()->withErrors(['email' => [__($status)]]);
    }

    public function showVerifyCodeForm(Request $request)
    {


        $email = session('email');
        if (!$email) {
            return redirect()->route('forgot-password')->withErrors(['email' => 'Session expired. Please try again.']);
        }
        return view('components.verif-email', compact('email'));
    }


    public function verifyCode(Request $request)
    {


        $request->validate([
            'digit1' => 'required|numeric|digits:1',
            'digit2' => 'required|numeric|digits:1',
            'digit3' => 'required|numeric|digits:1',
            'digit4' => 'required|numeric|digits:1',
        ]);

        // Gabungkan 4 digit menjadi kode
        $verificationCode = $request->digit1 . $request->digit2 . $request->digit3 . $request->digit4;

        $email = session('email');
        if (!$email) {
            return redirect()->route('forgot-password')->withErrors(['email' => 'Session expired. Please try again.']);
        }

        $resetEntry = DB::table('password_reset_tokens')
            ->where('email', session('email'))
            ->where('verification_code',  $verificationCode)
            ->first();

        if (!$resetEntry) {
            return back()->withErrors(['verification_code' => 'Invalid verification code.']);
        }

        return redirect()->route('reset-password')->with('email', $request->email);
    }

    public function resendCode()
    {
        $email = session('email');

        if (!$email) {
            return redirect()->route('forgot-password')->withErrors(['email' => 'Session expired. Please try again.']);
        }

        return $this->sendVerificationCode(new Request(['email' => $email]));
    }


    public function showResetPasswordForm(Request $request)
    {


        $email = session('email');

        $verificationCode = session('verification_code');

        if (!$email) {
            return redirect()->route('forgot-password')->withErrors(['email' => 'Session expired. Please try again.']);
        }

        return view('components.new-pw', compact('email'));
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'password' => 'required|string|confirmed|min:8',
        ]);

        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        $user = \App\Models\User::where('email', $request->email)->first();
        $user->update(['password' => Hash::make($request->password)]);

        return redirect('/')->with('status', 'Password successfully reset.');
    }
}
