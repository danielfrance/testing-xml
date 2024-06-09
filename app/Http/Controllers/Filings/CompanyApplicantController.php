<?php

namespace App\Http\Controllers\Filings;

use App\Http\Controllers\Controller;
use App\Models\CompanyApplicant;
use App\Models\Country;
use App\Models\File;
use App\Models\Filing;
use App\Models\State;
use App\Models\Tribe;
use App\Services\FileService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class CompanyApplicantController extends Controller
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
        //
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
    public function store(Request $request, string $id)
    {

        
        try {
            $data = $request->all();

            if ($data['id_type'] === 'us_passport') {
                $data['id_document_state'] = null;
                $data['id_document_tribe'] = null;
            }

            $validator = $this->validateCompanyApplicant($data, 'store');

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            $filing = Filing::teamOwned($this->user->team_id, $id);

            $applicant = DB::transaction(function () use ($data, $request, $filing) {
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
                $applicant = new CompanyApplicant();
                $applicant->fill($data);
                $applicant->team_id = $this->user->team_id;
                $applicant->save();

                return $applicant;
            });


            $filing->companyApplicants()->attach($applicant, ['created_at' => now(), 'updated_at' => now()]);

            if ($request->action == 'save_exit') {
                return redirect()->route('filing.index')->with('success', 'Company Applicant saved successfully');
            } else {
                return redirect()->route('filing.applicants.show', $filing->id)->with('success', 'Company Applicant saved successfully');
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

        // if companyInfo -> existing_reporting_company === true, then page is not required and should be skipped but incase someone tries to access it, disable all fields and dont submit the form.


        $filing = Filing::findOrFail($id);
        $applicants = CompanyApplicant::where('team_id', $filing->team_id)->get();
        $filteredApplicants = $applicants->filter(function ($applicant) use ($filing) {
            return !$filing->companyApplicants->contains($applicant);
        });

        return Inertia::render('Filings/CompanyApplicant', [
            'filing' => $filing,
            'companyInfo' => $filing->companyInfo,
            'applicants' => $filing->companyApplicants,
            'teamApplicants' => $filteredApplicants->values()->all(),
            'existingReportingCompany' => $filing->companyInfo->existing_reporting_company,
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
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $filing_id, string $applicant_id)
    {

        try {
            $applicant = CompanyApplicant::findOrFail($applicant_id);

            $data = $request->all();
            $data['team_id'] = $this->user->team_id;

            if ($data['id_type'] === 'us_passport') {
                $data['id_document_state'] = null;
                $data['id_document_tribe'] = null;
            }

            $validator = $this->validateCompanyApplicant($data, 'update', $applicant_id);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            DB::transaction(function () use ($data, $applicant, $request) {
                if ($request->hasFile('file')) {
                    // check if the applicant already has a file
                    if ($applicant->file) {
                        // delete the file from the storage
                        $this->fileService->deleteFile($applicant->file->path);
                        // delete the file from the database
                        $applicant->file->delete();
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

                $applicant->update($data);
                return $applicant;
            });

            if ($request->action == 'save_exit') {
                return redirect()->route('filing.index')->with('success', 'Company Applicant updated successfully');
            } else {
                return redirect()->back()->with('success', 'Company Applicant updated successfully');
            }
        } catch (\Throwable $th) {
            // dd($th);
            throw $th;
        }
    }


    public function updateAndExit(Request $request, string $id, string $applicant_id)
    {
        $request->merge(['action' => 'save_exit']);
        return $this->update($request, $id, $applicant_id);
    }




    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }


    public function addToFiling(string $filing_id, string $applicant_id)
    {
        try {
            $filing = Filing::findOrFail($filing_id);
            $applicant = CompanyApplicant::findOrFail($applicant_id);

            $filing->companyApplicants()->attach($applicant, ['created_at' => now(), 'updated_at' => now()]);


            return redirect()->back()->with('success', 'Applicant assigned to Filing successfully');
        } catch (\Throwable $th) {
            throw $th;
            return redirect()->back()->with('error', 'There was a problem assigning the applicant to the filing. Please try again.');
        }
    }

    public function removeFromFiling(string $filing_id, string $applicant_id)
    {
        try {
            $filing = Filing::findOrFail($filing_id);
            $applicant = CompanyApplicant::findOrFail($applicant_id);

            $filing->companyApplicants()->detach($applicant);

            return redirect()->back()->with('success', 'Applicant removed from Filing successfully');
        } catch (\Throwable $th) {
            throw $th;
            return redirect()->back()->with('error', 'There was a problem removing the applicant from the filing. Please try again.');
        }
    }

    public function validateCompanyApplicant($data, $action = 'store', $applicant_id = null)
    {
        $USA = Country::where('name', 'United States')->first();

        try {
            $attributes = [
                'fincen_id' => 'Applicant FinCEN ID',
                'last_name' => 'Applicant Last Name',
                'first_name' => 'Applicant First Name',
                'middle_name' => 'Applicant Middle Name',
                'suffix' => 'Applicant Suffix',
                'dob' => 'Applicant Date of Birth',
                'address_type' => 'Applicant Current Address Type',
                'address' => 'Applicant Current Address',
                'city' => 'Applicant Current City',
                'state_id' => 'Applicant Current State',
                'country_id' => 'Applicant Current Country',
                'zip' => 'Applicant Current Zip Code',
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

            if ($action === 'update') {
                $applicant = CompanyApplicant::teamOwned($this->user->team_id, $applicant_id);

                if (!$applicant) {
                    abort(404);
                }
                if ($applicant && $applicant->file) {
                    $rules['file'] = 'nullable';
                }
            }


            $validator = Validator::make($data, $rules, [], $attributes);

            return $validator;
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
