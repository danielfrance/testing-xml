<?php

namespace App\Http\Controllers\Team;

use App\Http\Controllers\Controller;
use App\Models\Invite;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Illuminate\Support\Str;


class MemberController extends Controller
{
    protected $user;

    public function __construct(Request $request)
    {
        $this->user = auth()->user();
        // if the user is a super admin or admin, they can continue else they will be redirected to the dashboard

    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        $members = User::where('team_id', $this->user->team->id)->get();
        $invitedMembers = Invite::where('team_id', $this->user->team->id)
            ->where('type', 'team_member')
            ->whereIn('status', ['pending', 'expired'])
            ->get();

        // dd($invitedMembers);

        // merge members and invited members
        $members = $members->merge($invitedMembers);
        // structure the members in a way that the frontend can easily render
        $members = $members->map(function ($member) {
            return [
                'id' => $member->id,
                'name' => $member->name,
                'email' => $member->email,
                'role' => Str::ucfirst($member->role_name) ?? '',
                'status' => $member->status ?? 'active',
                'created_at' => $member->created_at,
            ];
        });

        return Inertia::render('Team/Members/MembersIndex', [
            'members' => $members,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $member = User::teamOwned($this->user->team->id, $id);
        // TODO: in the future we wont pass the superadmin role in as that will be a protected role
        $roles = Role::all();

        return Inertia::render('Team/Members/MemberEdit', [
            'member' => $member,
            'roles' => $roles,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $member = User::teamOwned($this->user->team->id, $id);

            $validator = Validator::make($request->all(), [
                'role_id' => 'required',
                'name' => 'required',
                'email' => 'required|email|unique:users,email,' . $member->id,
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            DB::transaction(function () use ($request, $member) {
                $member->update([
                    'name' => $request->name,
                    'email' => $request->email,
                ]);

                // TODO: in the future we wont pass the superadmin role in as that will be a protected role
                $role = Role::find($request->role_id);
                // remove previous role
                $member->roles()->detach();
                // assign new role
                $member->addRole($role->name, $this->user->team->id);
            });



            return redirect()->back()->with('success', 'Member updated successfully');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function passwordReset(string $id)
    {
        try {
            $member = User::teamOwned($this->user->team->id, $id);

            $status = Password::sendResetLink(['email' => $member->email]);

            if ($status == Password::RESET_LINK_SENT) {
                return back()->with('success', __($status));
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function invitedTeamMemberEdit(string $slug, string $token)
    {

        try {
            $invite = Invite::where('token', $token)->first();

            //if invite was soft deleted
            if ($invite->deleted_at) {
                return redirect()->route('invite.completed');
            }

            if (!$invite) {
                abort(404);
            }
            if (Carbon::now()->greaterThan($invite->expires_at)) {
                $invite->status = 'expired';
                $invite->save();
                return redirect()->route('invite.expiredToken', ['invite_token' => $invite->token]);
            }



            return Inertia::render('Team/Members/InvitedMember', [
                'invite' => $invite,
            ]);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function invitedTeamMemberStore(Request $request)
    {
        try {
            // validate that invite exists
            $invite = Invite::where('token', $request->token)->first();

            if (!$invite) {
                abort(404);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|confirmed|min:8',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }


            DB::transaction(function () use ($request, $invite) {
                $user = User::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' => bcrypt($request->password),
                    'team_id' => $invite->team_id,
                    'email_verified_at' => now(),
                ]);

                // assign everyone the role of user to start with

                $invite->status = 'accepted';
                $invite->save();
                $invite->delete();

                $role = Role::where('name', 'user')->first();

                $user->addRole($role, $invite->team_id);
            });

            return redirect()->route('login')->with('success', 'Account created successfully');
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
