<?php

namespace App\Http\Controllers;

use App\Models\BeneficialOwner;
use App\Models\Country;
use App\Models\File;
use App\Models\Invite;
use App\Models\State;
use App\Models\Tribe;
use App\Services\FileService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Illuminate\Support\Str;


class OwnerController extends Controller
{

    public $user;
    public $fileService;

    public function __construct(Request $request, FileService $fileService)
    {
        $this->user = auth()->user();
        $this->fileService = $fileService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $owners = BeneficialOwner::where('team_id', $this->user->team_id)->get();
        $invitees = Invite::where('team_id', $this->user->team_id)
            ->where('type', 'owner')
            ->where('status', '!=', 'accepted')
            ->get();

        return Inertia::render('Owners/OwnersIndex', [
            'owners' => $owners,
            'invitees' => $invitees,

        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Owners/OwnersCreate', [
            'countries' => Country::all(),
            'states' => State::all(),
            'tribes' => Tribe::all()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

       
        try {
            $data = $request->all();
            $validator = $this->validateBeneficialOwner($data);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            $owner = DB::transaction(function () use ($data, $request) {
                if ($request->hasFile('file')) {
                    $url = $this->fileService->uploadFile($this->user->team_id, $request->file('file'));

                    // save the file url to the database
                    $newFile = File::create([
                        'team_id' => $this->user->team_id,
                        'path' => $url,
                        'name' => $request->file('file')->getClientOriginalName(),
                    ]);
                }
                $data['id_document_file_id'] = isset($newFile) ? $newFile->id : null;

                $owner = new BeneficialOwner();
                $owner->fill($data);
                $owner->team_id = $this->user->team_id;
                $owner->save();

                return $owner;
            });

            

            return redirect()->route('owners.edit', $owner->id)->with('success', 'Owner created successfully');
        } catch (\Throwable $th) {
            throw $th;
        }
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
        $owner = BeneficialOwner::teamOwned($this->user->team_id, $id);

        return Inertia::render('Owners/OwnersEdit', [
            'owner' => $owner,
            'countries' => Country::all(),
            'states' => State::all(),
            'tribes' => Tribe::all()
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        try {
            $data = $request->all();
            $owner = BeneficialOwner::teamOwned($this->user->team_id, $id);

            if (!$owner) {
                abort(404);
            }

            $validator = $this->validateBeneficialOwner($data, 'update', $id);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            $owner = DB::transaction(function () use ($data, $owner, $request) {
                if ($request->hasFile('file')) {
                    // check if the owner already has a file
                    if ($owner->file) {
                        // delete the file from the storage
                        $this->fileService->deleteFile($owner->file->path);
                        // delete the file from the database
                        $owner->file->delete();
                    }
                    $url = $this->fileService->uploadFile($this->user->team_id, $request->file('file'));

                    // save the file url to the database
                    $newFile = File::create([
                        'team_id' => $this->user->team_id,
                        'path' => $url,
                        'name' => $request->file('file')->getClientOriginalName(),
                    ]);
                }
                $data['id_document_file_id'] = isset($newFile) ? $newFile->id : null;

                $owner->update($data);
                return $owner;
            });

            return redirect()->back()->with('success', 'Owner updated successfully');
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

    public function sendMagicLink(Request $request)
    {
        $data = $request->all();
        $owner = BeneficialOwner::where('email', $data['email'])->first();
        $token = Str::random(60);
        $expiration = Carbon::now()->addHours(36);

        if ($owner) {
            return redirect()->back()->with('error', 'Owner already exists');
        }


        return redirect()->back()->with('success', 'Magic link sent successfully');
    }

    public function invitedOwnerEdit($slug, $token)
    {
        $invite = Invite::where('token', $token)->withTrashed()->first();

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


        $invite->status = 'accepted';
        $invite->save();

        return Inertia::render('Owners/InvitedOwner', [
            'invite' => $invite,
            'countries' => Country::all(),
            'states' => State::all(),
            'tribes' => Tribe::all()
        ]);
    }

    public function invitedOwnerStore(Request $request)
    {

        $invite = Invite::where('token', $request->token)->first();
        $teamID = $request->team_id;

        // make sure team_id is the same as the team_id in the invite
        if ($invite->team_id != $teamID) {
            abort(404);
        }

        try {
            $validator = $this->validateBeneficialOwner($request->all(), 'store');

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            $owner = DB::transaction(function () use ($request, $invite, $teamID) {

                if ($request->hasFile('file')) {
                    $url = $this->fileService->uploadFile($teamID, $request->file('file'));

                    // save the file url to the database
                    $newFile = File::create([
                        'team_id' => $teamID,
                        'path' => $url,
                        'name' => $request->file('file')->getClientOriginalName(),
                    ]);

                    $id_document_file_id = isset($newFile) ? $newFile->id : null;
                }
                $owner = new BeneficialOwner();
                $owner->fill($request->all());
                $owner->id_document_file_id = $id_document_file_id ?? null;
                $owner->save();

                $invite->status = 'completed';
                $invite->deleted_at = now();
                $invite->save();

                return $owner;
            });

            return redirect()->back()->with('success', 'Your information has been successfully saved. Our team will reach out with further instructions or questions. Thank you!');
        } catch (\Throwable $th) {
            throw $th;
        }

    }

    public function validateBeneficialOwner($data, $action = 'store', $owner_id = null)
    {
        $USA = Country::where('name', 'United States')->first();

        try {
            

            $attributes = [
                'parent_guardian',
                'exempt_entity',
                'fincen_id' => 'Owner FinCEN ID',
                'last_name' => 'Owner Last Name',
                'first_name' => 'Owner First Name',
                'middle_name' => 'Owner Middle Name',
                'suffix' => 'Owner Suffix',
                'dob' => 'Owner Date of Birth',
                'address' => 'Owner Current Address',
                'city' => 'Owner Current City',
                'state_id' => 'Owner Current State',
                'country_id' => 'Owner Current Country',
                'zip' => 'Owner Current Zip Code',
                'id_type' => 'Identification Document Type',
                'id_number' => 'Identification Document Number',
                'id_document_country' => 'Identification Document Issuing Country',
                'id_document_state' => 'Identification Document Issuing State',
                'id_document_tribe' => 'Tribal Jurisdiction of Formation',
                'tribal_other_name' => 'Name of Other Tribe',
            ];

            // if fince_id is not empty, it must be 12 characters long, no more, no less
            // if fincen_id is not empty and validated, all other fields are optional
            // if fincen_id is empty, all other fields are required
            $rules = [
                'fincen_id' => 'nullable|size:12',
                'last_name' => 'required_without:fincen_id',
                'first_name' => 'required_without:fincen_id',
                'middle_name' => 'required_without:fincen_id',
                'dob' => 'required_without:fincen_id',
                'address' => 'required_without:fincen_id',
                'city' => 'required_without:fincen_id',
                'country_id' => 'required_without:fincen_id',
                'zip' => 'required_without:fincen_id',
                'id_type' => 'required_without:fincen_id',
                'id_number' => 'required_without:fincen_id',
                'id_document_country' => 'required_without:fincen_id',
                'file' => 'required_without:fincen_id',
            ];

            if ($owner_id) {
                $owner = BeneficialOwner::teamOwned($this->user->team_id, $owner_id);
                if (!$owner) {
                    abort(404);
                }
                if (
                    $owner && $owner->file
                ) {
                    $rules['file'] = 'nullable';
                }
            }

            //if id_type === us_passport, id_document_state and id_document_tribe must be null
            if ($data['id_type'] === 'us_passport') {
                $rules['id_document_state'] = 'nullable';
                $rules['id_document_tribe'] = 'nullable';
            }
            if (!$data['fincen_id'] && ($data['id_type'] === 'drivers_license' || $data['id_type'] === 'state_tribe_id')) {
                $rules['id_document_state'] = 'required_without:id_document_tribe';
                $rules['id_document_tribe'] = 'required_without:id_document_state';
            }

            if ($data['country_id'] !== $USA->id) {
                $rules['state_id'] = 'nullable';
            }

            

            $validator = Validator::make($data, $rules, [], $attributes);

            return $validator;
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
