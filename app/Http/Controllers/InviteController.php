<?php

namespace App\Http\Controllers;

use App\Mail\InviteMailable;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\Invite;
use App\Notifications\InviteMailNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class InviteController extends Controller
{
    public $user;

    public function __construct()
    {
        $this->user = auth()->user();
    }

    public function sendInvite(Request $request)
    {

        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'type' => 'required',
                'name' => 'required',
            ]);

            if ($validator->fails()) {
                Log::info('Invite owner validation failed');
                Log::info($validator->errors());
                return redirect()->back()->with('error', 'Please enter a valid name & email address');
            }


            $token = Str::random(60);
            //convert to int
            $hours = (int)config('auth.passwords.magic_links.expire');
            $expiresAt = Carbon::now()->addHours($hours);


            DB::transaction(function () use ($request, $token, $expiresAt) {
                $invite = Invite::create([
                    'email' => $request->email,
                    'type' => $request->type,
                    'team_id' => $this->user->team_id,
                    'token' => $token,
                    'expires_at' => $expiresAt,
                    'name' => $request->name,
                ]);

                // $invite->notify(new InviteMailNotification($invite));
                Mail::to($request->email)->send(new InviteMailable($invite));
            });
            return redirect()->back()->with('success', 'Invitation sent successfully!');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function resendInvite($id)
    {
        try {
            $invite = Invite::find($id);
            $invite->token = Str::random(60);
            $invite->status = 'pending';
            $invite->expires_at = Carbon::now()->addHours(24);
            $invite->save();

            $invite->notify(new InviteMailNotification($invite));
            return redirect()->back()->with('success', 'Invitation resent successfully!');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function showExpiredTokenPage(Request $request)
    {
        try {
            $invite = Invite::where('token', $request->query('invite_token'))->firstOrFail();

            if (!$invite) {
                abort(404);
            }
            // return view('auth.invite-expired', ['invite' => $invite]);
            return Inertia::render('Auth/ExpiredInvite', ['invite' => $invite]);
        } catch (\Throwable $th) {
            throw $th;
        }
    }


    public function requestNewToken($token)
    {
        $invite = Invite::where('token', $token)->firstOrFail();
        $invite->status = 'requested';
        $invite->save();

        return redirect()->back()->with('success', 'We have received your request. Please wait for the admin to send you a new invite.');
    }
}
