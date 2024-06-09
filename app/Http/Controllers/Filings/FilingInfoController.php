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
use Faker\Provider\ar_EG\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Inertia\Inertia;

class FilingInfoController extends Controller
{
    public $user;

    public function __construct()
    {
        $this->user = auth()->user();
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = $this->user;

        $filings = Filing::where('team_id', $user->team_id)
            ->with(['companyInfo:filing_id,legal_name,tax_id_number'])
            ->orderBy('created_at', 'asc')
            ->get();


        return Inertia::render('Filings/FilingIndex', [
            'filings' => $filings
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Filings/FilingInformation', [
            'filing' => new Filing(),
            'countries' => Country::select('id', 'name')->get(),
            'formType' => 'create',
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */

    public function store(Request $request)
    {
        // if it's an initial report, the legal name, tax id type, and tax id number are going to be null
        // so just create the filing and direct to the next page

        try {
            $action = $request->input('action');
            $data = $request->except('action');
            $data['team_id'] = auth()->user()->team_id;


            $validator = $this->validateFilingInfo($data);
            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }


            $filing = DB::transaction(function () use ($data) {
                $newFiling = Filing::create([
                    'filing_type_id' => $data['filing_type_id'],
                    'team_id' => $data['team_id'],
                ]);

                return $newFiling;
            });



            if ($action == 'save_exit') {
                return redirect()->route('filing.index')->with('success', 'Filing information saved successfully');
            } else {
                return redirect()->route('filing.company_info.show', $filing->id)->with('success', 'Filing information saved successfully');
            }
        } catch (\Throwable $th) {
            return redirect()->back()->withErrors($th->getMessage())->withInput();
        }
    }

    public function storeAndExit(Request $request)
    {
        $request->merge(['action' => 'save_exit']);
        return $this->store($request);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = $this->user;

        // Use the model scope for team ownership check
        $filing = Filing::teamOwned($user->team_id, $id);
        $companyInfo = CompanyInfo::where('filing_id', $filing->id)->first();

        if (!$filing) {
            // TODO: customize the 404 page and error message
            abort(404);
        }

        return Inertia::render('Filings/FilingInformation', [
            'filing' => $filing,
            'companyInfo' => $companyInfo,
            'countries' => Country::all(),
            'states' => State::all(),
            'tribes' => Tribe::all(),
            'formType' => 'edit',
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
    public function update(Request $request, string $id)
    {
        try {
            $user = $this->user;
            $action = $request->input('action');
            $data = $request->except('action');

            // Use the model scope for team ownership check
            $filing = Filing::teamOwned($user->team_id, $id);


            if (!$filing) {
                abort(404);
            }

            $validator = $this->validateFilingInfo($request->all(), $filing->id);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            } 

            DB::transaction(function () use ($data, $filing) {
                $filing->update([
                    'filing_type_id' => $data['filing_type_id'],
                ]);

                // $companyInfo = CompanyInfo::updateOrCreate(
                //     ['filing_id' => $filing->id],
                //     [
                //         'legal_name' => $data['legal_name'],
                //         'tax_id_type_id' => $data['tax_id_type_id'],
                //         'tax_id_number' => $data['tax_id_number'],
                //         'tax_id_country_id' => $data['tax_id_country_id'],
                //     ]
                // );
            });


            if ($action == 'save_exit') {
                return redirect()->route('filing.index')->with('success', 'Filing information saved successfully');
            } else {
                return redirect()->route('filing.company_info.show', $filing->id)->with('success', 'Filing information saved successfully');
            }
        } catch (\Throwable $th) {
            throw $th;
            return redirect()->back()->withErrors($th->getMessage())->withInput();
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



    public function validateFilingInfo($data, $filingId = null)
    {
        $attributes = [
            // 'legal_name' => 'Legal Name',
            // 'tax_id_type_id' => 'Tax ID Type',
            // 'tax_id_number' => 'Tax ID Number',
            // 'country_id' => 'Filing Country',
        ];

        $rules = [
            'filing_type_id' => 'required|in:1,2,3,4',
            // 'legal_name' => $data['filing_type_id'] != 1 ? 'required' : 'nullable',
            // 'tax_id_type_id' => $data['filing_type_id'] != 1 ? 'required' : 'nullable',
            // 'tax_id_number' => $data['filing_type_id'] != 1 ? 'required' : 'nullable',
            // 'tax_id_country_id' => $data['filing_type_id'] != 1 ? 'required' : 'nullable',
        ];

        // if ($data['filing_type_id'] != 1) {
        //     if ($filingId) {
        //         if ($data['tax_id_type_id'] == '1') {
        //             $rules['tax_id_number'] .= '|unique:company_info,tax_id_number,' . $filingId;
        //         }
        //         if ($data['tax_id_type_id'] == '2') {
        //             $rules['tax_id'] .= '|unique:company_info,tax_id_number,' . $filingId;
        //         }
        //     } else {
        //         if ($data['tax_id_type_id'] == '1') {
        //             $rules['tax_id'] .= '|unique:company_info,tax_id_number';
        //         }
        //         if ($data['tax_id_type_id'] == '2') {
        //             $rules['tax_id'] .= '|unique:company_info,tax_id_number';
        //         }
        //     }
        // }
        return Validator::make($data, $rules, [], $attributes);
    }

    public function review(string $id)
    {
        $user = $this->user;

        // Use the model scope for team ownership check
        $filing = Filing::teamOwned($user->team_id, $id);


        if (!$filing) {
            abort(404);
        }

        return Inertia::render('Filings/FilingReview', [
            'filing' => $filing,
            'companyInfo' => $filing->companyInfo,
            'companyApplicants' => $filing->companyApplicants,
            'beneficialOwners' => $filing->beneficialOwners,

        ]);
    }

    public function submitFiling(string $id)
    {
        $user = $this->user;

        // Use the model scope for team ownership check
        $filing = Filing::teamOwned($user->team_id, $id);

        if (!$filing) {
            abort(404);
        }

        $filing->update([
            'status' => 'Submitted',
        ]);

        return redirect()->route('filing.index')->with('success', 'Filing has been submitted. Check your email for confirmation.');
    }
}
