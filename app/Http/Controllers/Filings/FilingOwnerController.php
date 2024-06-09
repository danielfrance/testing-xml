<?php

namespace App\Http\Controllers\Filings;

use App\Http\Controllers\Controller;
use App\Models\BeneficialOwner;
use App\Models\CompanyInfo;
use App\Models\Country;
use App\Models\File;
use App\Models\Filing;
use App\Models\FilingType;
use App\Models\State;
use App\Models\TaxIDType;
use App\Models\Tribe;
use App\Services\FileService;
use Faker\Provider\ar_EG\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Inertia\Inertia;

class FilingOwnerController extends Controller
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
        $user = $this->user;
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Filings/FilingOwner', [
            'filing' => new Filing(),
            'filingTypes' => FilingType::select('id', 'name')->get(),
            'taxIdTypes' => TaxIDType::select('id', 'name')->get(),
            'countries' => Country::select('id', 'name')->get(),
            'formType' => 'create',
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, string $id)
    {
        try {
            $data = $request->all();

            if ($data['id_type'] === 'us_passport') {
                $data['id_document_state'] = null;
                $data['id_document_tribe'] = null;
            }

            $validator = $this->validateBeneficialOwner($data, 'store');

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }



            $filing = Filing::teamOwned($this->user->team_id, $id);



            DB::transaction(function () use ($data, $request, $filing) {
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

                $filing->beneficialOwners()->attach($owner, ['created_at' => now(), 'updated_at' => now()]);
            });

            if ($request->action == 'save_exit') {
                return redirect()->route('filing.index')->with('success', 'Beneficial Owner updated successfully');
            } else {
                return redirect()->route('filing.owners.show', $filing->id)->with('success', 'Beneficial Owner saved successfully');
            }
        } catch (\Throwable $th) {

            throw $th;
        }
    }

    public function storeAndExit(Request $request, string $id)
    {
        $request->merge(['action' => 'save_exit']);
        return $this->store($request, $id);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $filing = Filing::findOrFail($id);
        $owners = BeneficialOwner::where('team_id', $filing->team_id)->get();

        $filteredOwners = $owners->filter(function ($owner) use ($filing) {
            return !$filing->beneficialOwners->contains($owner);
        });

        // dd(gettype($filteredOwners), gettype($owners), gettype($filteredOwners->values()->all()));

        return Inertia::render('Filings/BeneficialOwner', [
            'filing' => $filing,
            'beneficialOwners' => $filing->beneficialOwners,
            'formType' => 'edit',
            'allOwners' => $filteredOwners->values()->all(),
            'countries' => Country::all(),
            'states' => State::all(),
            'tribes' => Tribe::all(),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $user = $this->user;
        $filing = Filing::teamOwned($user->team_id, $id);
        $companyInfo = CompanyInfo::where('filing_id', $filing->id)->first();
        $countries = Country::all();
        $taxIdTypes = TaxIDType::all();

        return Inertia::render('Filings/FilingOwner', [
            'filing' => $filing,
            'companyInfo' => $companyInfo,
            'countries' => $countries,
            'taxIdTypes' => $taxIdTypes,
            'formType' => 'edit',
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $filing_id, string $owner_id)
    {
        try {
            $owner = BeneficialOwner::findOrFail($owner_id);
            $data = $request->all();

            if ($data['id_type'] === 'us_passport') {
                $data['id_document_state'] = null;
                $data['id_document_tribe'] = null;
            }


            $validator = $this->validateBeneficialOwner($data, 'update', $owner_id);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }


            DB::transaction(function () use ($data, $owner, $request) {
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


            if ($request->action == 'save_exit') {
                return redirect()->route('filing.index')->with('success', 'Beneficial Owner updated successfully');
            } else {
                return redirect()->back()->with('success', 'Beneficial Owner updated successfully');
            }
        } catch (\Throwable $th) {

            throw $th;
        }
    }

    public function validateBeneficialOwner($data, $action = 'store', $owner_id = null)
    {
        try {
            $USA = Country::where('name', 'United States')->first();


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
                'fincen_id_confirmation' => 'confirmed|required_with:fincen_id',
                'last_name' => 'required_without:fincen_id',
                'first_name' => 'required_without:fincen_id',
                'middle_name' => 'required_without:fincen_id',
                'dob' => 'required_without:fincen_id',
                'address' => 'required_without:fincen_id',
                'city' => 'required_without:fincen_id',
                'state_id' => 'required_without:fincen_id',
                'country_id' => 'required_without:fincen_id',
                'zip' => 'required_without:fincen_id',
                'id_type' => 'required_without:fincen_id',
                'id_number' => 'required_without:fincen_id',
                'id_document_country' => 'required_without:fincen_id',
                'file' => 'required_without:fincen_id',
            ];


            //if id_type === us_passport, id_document_state and id_document_tribe must be null
            if ($data['id_type'] === 'us_passport') {
                $rules['id_document_state'] = 'nullable';
                $rules['id_document_tribe'] = 'nullable';
            }
            if ($data['id_type'] === 'drivers_license' || $data['id_type'] === 'state_tribe_id') {
                $rules['id_document_state'] = 'required_without:id_document_tribe';
                $rules['id_document_tribe'] = 'required_without:id_document_state';
            }
            if ($data['country_id'] !== $USA->id) {
                $rules['state_id'] = 'nullable';
            }
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

            $validator = Validator::make($data, $rules, [], $attributes);

            return $validator;
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function addToFiling(string $filing_id, string $owner_id)
    {
        try {
            $filing = Filing::findOrFail($filing_id);
            $owner = BeneficialOwner::findOrFail($owner_id);

            $filing->beneficialOwners()->attach($owner, ['created_at' => now(), 'updated_at' => now()]);


            return redirect()->back()->with('success', 'Owner assigned to Filing successfully');
        } catch (\Throwable $th) {
            throw $th;
            return redirect()->back()->with('error', 'There was a problem assigning the owner to the filing. Please try again.');
        }
    }

    public function removeFromFiling(string $filing_id, string $owner_id)
    {
        try {
            $filing = Filing::findOrFail($filing_id);
            $owner = BeneficialOwner::findOrFail($owner_id);

            $filing->beneficialOwners()->detach($owner);

            return redirect()->back()->with('success', 'Owner removed from Filing successfully');
        } catch (\Throwable $th) {
            throw $th;
            return redirect()->back()->with('error', 'There was a problem removing the owner from the filing. Please try again.');
        }
    }
}
