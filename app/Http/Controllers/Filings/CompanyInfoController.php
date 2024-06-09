<?php

namespace App\Http\Controllers\Filings;

use App\Http\Controllers\Controller;
use App\Models\CompanyInfo;
use App\Models\Country;
use App\Models\Filing;
use App\Models\FilingType;
use App\Models\State;
use App\Models\TaxIDType;
use App\Models\Tribe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Fluent;
use Inertia\Inertia;

class CompanyInfoController extends Controller
{
    public $filing;
    public $user;

    public function __construct(Request $request)
    {
        $this->filing = Filing::findOrFail($request->id);
        $this->user = auth()->user();
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

        $USA = Country::where('name', 'United States')->first();


        $filing = Filing::teamOwned($this->user->team_id, $id);

        if (!$filing) {
            abort(404);
        }

        $formationCountry = Country::findOrFail($request->country_formation_id);

        try {
            $data = $request->except('action');

            if ($formationCountry->id !== $USA->id && !$formationCountry->us_territory) {
                $data['state_formation_id'] = null;
                $data['tribal_formation_id'] = null;
            }


            $validator = $this->validateCompanyInfo($data, $id, 'store');

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            } else {
                $companyInfo = new CompanyInfo();
                $companyInfo->fill($data);
                $companyInfo->filing_id = $id;
                $companyInfo->team_id = $filing->team_id;
                $companyInfo->save();

                if ($request->action == 'save_exit') {
                    return redirect()->route('filing.index')->with('success', 'Company information saved successfully');
                } else {
                    return redirect()->route('filing.applicants.show', $id);
                }
            }
        } catch (\Throwable $th) {
            throw $th;
            Log::error($th->getMessage());
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
        $user = $this->user;

        // Use the model scope for team ownership check
        $filing = Filing::teamOwned($user->team_id, $id);
        $taxIdTypes = TaxIDType::select('id', 'name')->get();
        $countries = Country::select('id', 'name', 'us_territory')->get();
        $states = State::select('id', 'name')->get();
        $tribes = Tribe::select('id', 'name')->get();

        if (!$filing) {
            abort(404);
        }

        return Inertia::render('Filings/CompanyInfo/CompanyInformation', [
            'filing' => $filing,
            'companyInfo' => $filing->companyInfo,
            'taxIdTypes' => $taxIdTypes,
            'countries' => $countries,
            'states' => $states,
            'tribes' => $tribes,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        $formationCountry = Country::findOrFail($request->country_formation_id);
        $USA = Country::where('name', 'United States')->first();


        try {
            $data = $request->except(['action', 'info_id']);

            if ($formationCountry->id != $USA->id && !$formationCountry->us_territory) {
                $data['state_formation_id'] = null;
                $data['tribal_formation_id'] = null;
            }



            $validator = $this->validateCompanyInfo($data, $id, 'update');

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            } else {

                $this->filing->companyInfo()->update($data);

                if ($request->action == 'save_exit') {
                    return redirect()->route('filing.index')->with('success', 'Company information saved successfully');
                } else {
                    return redirect()->route('filing.applicants.show', $id);
                }
            }
        } catch (\Throwable $th) {
            throw $th;
            Log::error($th->getMessage());
        }
    }

    public function updateAndExit(Request $request, string $id)
    {
        $request->merge(['action' => 'save_exit']);
        return $this->update($request, $id);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function validateCompanyInfo($data, $filingId)
    {

        $filing = Filing::teamOwned($this->user->team_id, $filingId);
        $formationCountry = Country::findOrFail($data['country_formation_id']);
        $USA = Country::where('name', 'United States')->first();


        if (!$filing) {
            abort(404);
        }


        $attributes = [
            'legal_name' => 'Legal Name',
            'tax_id_type_id' => 'Tax ID Type',
            'tax_id_number' => 'Tax ID Number',
            'tax_id_country_id' => 'Tax ID Country',
            'country_formation_id' => 'Formation Country',
            'state_formation_id' => 'Formation State',
            'tribal_formation_id' => 'Formation Tribe',
            'tribal_other_name' => 'Formation Tribe Other Name',
            'current_street_address' => 'Street Address',
            'current_city' => 'City',
            'current_state_id' => 'State',
            'current_country_id' => 'Country',
            'zip' => 'Zip',
            'get_fincen' => 'FinCEN Request',
            'foreign_pooled_investment' => 'Foreign Pooled Investment',
            'existing_reporting_company' => 'Existing Reporting Company',
        ];

        $rules = [
            'legal_name' => 'required',
            'tax_id_type_id' => 'required|in:1,2,3',
            'tax_id_number' => 'required',
            'tax_id_country_id' => 'required',
            'country_formation_id' => 'required',
            'current_street_address' => 'required',
            'current_city' => 'required',
            'current_state_id' => 'required',
            'current_country_id' => 'required',
            'zip' => 'required',
            'tribal_other_name' => 'required_if:tribal_formation_id,577',

        ];




        if ($formationCountry->id = $USA->id || $formationCountry->us_territory) {
            $rules['state_formation_id'] = 'required_if:tribal_formation_id,=,null';
            $rules['tribal_formation_id'] = 'required_if:state_formation_id,=,null';
        } else {
            $rules['state_formation_id'] = 'nullable';
            $rules['tribal_formation_id'] = 'nullable';
        }

        if ($data['tax_id_type_id'] == 1 || $data['tax_id_type_id'] == 2) {
            $rules['tax_id_country_id'] = 'required|in:' . $USA->id;
        }


        // if ($filingId) {
        //     if ($data['tax_id_type_id'] == '1') {
        //         $rules['tax_id_number'] .= '|size:10|unique:company_info,tax_id_number,' . $filingId;
        //     }
        //     if ($data['tax_id_type_id'] == '2') {
        //         $rules['tax_id'] .= '|size:11|unique:company_info,tax_id_number,' . $filingId;
        //     }
        // } else {
        //     if ($data['tax_id_type_id'] == '1') {
        //         $rules['tax_id'] .= '|size:10|unique:company_info,tax_id_number';
        //     }
        //     if ($data['tax_id_type_id'] == '2') {
        //         $rules['tax_id'] .= '|size:11|unique:company_info,tax_id_number';
        //     }
        // }

        $messages = [
            // 'legal_name.same' => 'The legal name must match the existing filing record which is "' . $filing->legal_name . '" .',
            // 'tax_id_type_id.same' => 'The tax ID type must match the existing filing record which is "' . $filing->taxIDType->name . '".',
            // 'tax_id_number.same' => 'The tax ID number must match the existing filing record which is "' . $filing->tax_id . '".',
            // 'tax_id_country_id.same' => 'The tax ID country must match the existing filing record which is "' . $filing->country->name . '".',
            'tribal_other_name.required_if' => 'The tribal formation other name is required when the tribal formation is "Other".',
        ];

        $validator = Validator::make($data, $rules, $messages, $attributes);

        

        return $validator;
    }
}
