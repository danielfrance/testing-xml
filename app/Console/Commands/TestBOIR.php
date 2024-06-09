<?php

namespace App\Console\Commands;

use App\Models\BeneficialOwner;
use App\Models\CompanyApplicant;
use App\Models\Country;
use App\Models\File;
use App\Models\Filing;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Vdhicts\XmlValidator\Validator as XmlValidator;
use Spatie\ArrayToXml\ArrayToXml;
use Illuminate\Support\Str;


class TestBOIR extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'boir:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */



    public function handle()
    {

        // First we get a valid token

        $token = $this->getValidToken();

        // then we initiate a process. this returns a process ID
        // which is needed to upload files and send the filing XML

        // $process = $this->initiateProcess($token);
        // $processID = $process['processId'];

        // I dump this here just for testing purposes
        // $this->info($processID);
        // dd();

        // I grab a filing to test with
        $filing = Filing::find(2);


        // and hardcode the process id from above for testing
        $processID = 'BOIRPTZvfg4jdkthvYA7';

        // here we create the XML file
        // $this->createXML($token, $processID, $filing);

        //  and then we send the XML file to the API
        // $report = $this->sendFilingXML($token, $processID);
        // $this->info($report);

        // after a minute or so, we can check the status of the submission
        // $status = $this->getSubmissionStatus($token, $processID);
        // dd($status);

        // if the XML file passes validation, then we can get the transcript
        // if a filing passes validation, it could still be rejected
        // the reasons will be displayed in the transcript

        // $transcript = $this->getSubmissionTranscript($token, $processID);
        // dd($transcript);

        // then we can save the transcript PDF
        // $this->saveTranscriptPDF($transcript['pdfBinary']);
        // dd($transcript['status']);
    }

    public function getValidToken()
    {
        $cachedData = Cache::get('fincen_token');

        if ($cachedData) {
            $currentToken = $cachedData['token'];
            $expiresAt = $cachedData['expires_at'];

            // Check if the current time is less than the expiration time of the token
            if (now()->lessThan($expiresAt)) {
                return $currentToken;
            }
        }

        // If no valid token is found, create a new one
        return $this->createNewToken();
    }

    public function createNewToken()
    {
        $clientID = env("FINCEN_CLIENT_ID");
        $secret = env("FINCEN_CLIENT_SECRET");
        $url = "https://iam.fincen.gov/am/oauth2/realms/root/realms/Finance/access_token";


        $response = Http::withHeaders([
            'Authorization' => 'Basic ' . base64_encode($clientID . ':' . $secret),
            'Content-Type' => 'application/x-www-form-urlencoded'
        ])->asForm()->post($url, [
            'grant_type' => 'client_credentials',
            'scope' => 'BOSS-EFILE-SANDBOX'
        ]);

        $token = $response->json()['access_token'];
        $expiresIn = $response->json()['expires_in']; // Token expiration in seconds

        // Calculate the expiration time
        $expirationTime = now()->addSeconds($expiresIn)->subSeconds(5);

        // Store new token and its expiration time in cache
        Cache::put('fincen_token', ['token' => $token, 'expires_at' => $expirationTime], $expirationTime);


        return $token;
    }


    public function getFiling()
    {
        $filing = Filing::find(1);
        dd($filing->beneficialOwners[0]->file);
    }

    public function initiateProcess($token)
    {
        $api_url = env("FINCEN_API_URL");

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json'
        ])->get($api_url . '/processId');

        return $response->json();
        //   "processId" => "BOIRabmehNpi9N9Yur6T"

        // TODO: probably need to save this process ID to the filing
    }

    public function uploadFiles($token, $processID, $person)
    {
        $api_url = env("FINCEN_API_URL");
        try {
            $filePath = $person->file->path;
            $fileName = urlencode($person->file->name);

            // Determine the correct Content-Type based on the file extension
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $contentType = match ($fileExtension) {
                'jpeg', 'jpg' => 'image/jpeg',
                'png' => 'image/png',
                'pdf' => 'application/pdf',
                default => throw new Exception("Unsupported file type: {$fileExtension}")
            };

            $stream = Storage::disk('s3')->readStream($filePath);

            $endpoint = "$api_url/attachments/$processID/$fileName";


            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => $contentType
            ])->withBody($stream, $contentType)->post($endpoint);

            // Close the stream
            if (is_resource($stream)) {
                fclose($stream);
            }

            var_dump("api upload response: ", $response->json());
            if ($response->json('code') === 'upload_success') {
                return $person->file->name;
            }
        } catch (\Throwable $th) {
            dd($th->getMessage(), "Error with person ID: " . $person->id);
            throw $th;
        }
    }

    public function sendFilingXML($token, $processID)
    {
        $api_url = env("FINCEN_API_URL");
        $fileName = "test.xml";

        // URI encode the file name
        $encodedFileName = urlencode($fileName);

        // Construct the endpoint
        $endpoint = "$api_url/upload/BOIR/$processID/$encodedFileName";

        // Get XML binary data
        $xmlBinary = Storage::disk('local')->get("public/filings/$fileName");

        // Check if the XML data was retrieved successfully
        if (!$xmlBinary) {
            throw new Exception("Failed to retrieve XML file.");
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/xml'
            ])
                ->withBody($xmlBinary, 'application/xml')
                ->post($endpoint);

            // dd($response, $response->json());

            if ($response->successful()) {
                $this->info("success in sendFiling:");
                dd($response->json());
                // dd("XML is valid", $response->json());
            } else {
                $this->info("error in sendFiling:");
                dd($response->json());
            }
        } catch (\Throwable $th) {
            Log::error("Exception occurred: " . $th->getMessage());
            throw $th;
        }
    }

    public function validateXML()
    {

        $xml = storage_path('app/public/filings/test.xml');
        $xsd = storage_path('app/public/bior-filing-base.xsd');

        $xmlValidator = new XmlValidator();
        $result = $xmlValidator->validate($xml);

        if (!$result->isValid()) {
            dd($result->getErrors());
        }
    }

    public function getSubmissionStatus($token, $processID)
    {
        $api_url = env("FINCEN_API_URL");

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json'
        ])->get($api_url . '/submissionStatus/' . $processID);

        return $response->json();
    }

    public function getSubmissionTranscript($token, $processID)
    {
        $api_url = env("FINCEN_API_URL");

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json'
        ])->get($api_url . '/transcript/' . $processID);


        return $response->json();
    }

    public function saveTranscriptPDF($binary)
    {
        try {
            $binaryData = base64_decode($binary);
            $fileName = 'transcript.pdf';
            $path = 'public/transcripts/' . $fileName;

            Storage::disk('local')->put($path, $binaryData);


            return $path;
        } catch (\Throwable $th) {
            throw $th;
        }
    }


    public function createXML($token, $processID, $filing)
    {
        $USA = Country::where('name', 'United States')->first();


        try {
            $this->info('creating XML file');

            $EFilingPriorReportingCompanyIdentificationTypeCode = ($filing->companyInfo->tax_id_type_id === 3) ? '9' : (($filing->companyInfo->tax_id_type_id === 2) ? '1' : '2');

            $randSeqNum = rand(2, 400);

            $xmlData = [
                'fc2:SubmitterElectronicAddressText' => 'dfrance@bml.law',
                'fc2:SubmitterEntityIndivdualLastName' => 'France',
                'fc2:SubmitterIndivdualFirstName' => 'Derek',
                'fc2:Activity' => [
                    '_attributes' => [
                        'SeqNum' => $randSeqNum++
                    ],
                    'fc2:ApprovalOfficialSignatureDateText' => Carbon::parse($filing->prepared_date)->format('Ymd') ?? Carbon::now()->format('Ymd'),
                    'fc2:FilingDateText' => ''
                ],
            ];

            // Activity Section -- page 1 of the form
            if ($filing->filing_type_id != 1) {
                $xmlData['fc2:Activity'] += [
                    'fc2:EFilingPriorReportingCompanyIdentificationNumberText' => $filing->companyInfo->tax_id_number,
                    'fc2:EFilingPriorReportingCompanyIdentificationTypeCode' => $EFilingPriorReportingCompanyIdentificationTypeCode,
                    'fc2:EFilingPriorReportingCompanyIssuerCountryCodeText' => $filing->companyInfo->country_formation_code,
                    'fc2:EFilingPriorReportingCompanyName' => $filing->companyInfo->legal_name,
                ];
            }



            // Activity Association Section -- page 1 of the form -- type of BOIR being filed

            $xmlData['fc2:Activity'] += [
                'fc2:ActivityAssociation' => [
                    '_attributes' => [
                        'SeqNum' => $randSeqNum++,
                    ],
                ]
            ];
            if ($filing->filing_type_id === 1) {
                $xmlData['fc2:Activity']['fc2:ActivityAssociation']['fc2:InitialReportIndicator'] = 'Y';
            } elseif ($filing->filing_type_id === 2) {
                $xmlData['fc2:Activity']['fc2:ActivityAssociation']['fc2:CorrectsAmendsPriorReportIndicator'] = 'Y';
            } elseif ($filing->filing_type_id === 3) {
                $xmlData['fc2:Activity']['fc2:ActivityAssociation']['fc2:UpdatePriorReportIndicator'] = 'Y';
            } elseif ($filing->filing_type_id === 4) {
                $xmlData['fc2:Activity']['fc2:ActivityAssociation']['fc2:NewlyExemptEntityIndicator'] = 'Y';
            }

            // COMPANY INFO SECTION -- page 2 of the form

            $parties = [];

            $companyInfo = $this->generateCompanyInfo($filing->companyInfo, $randSeqNum++);

            array_push($parties, $companyInfo);

            // COMPANY APPLICANT SECTION -- page 3 of the form -- if filing->companyInfo->existing_reporting_company is true, skip this section
            if (!$filing->companyInfo->existing_reporting_company) {


                $applicants = $this->generateCompanyApplicants($token, $processID, $filing->companyApplicants, $randSeqNum++);

                array_push($parties, $applicants);
            }


            // BENEFICIAL OWNER SECTION -- page 4 of the form

            $owners = $this->generateBeneficialOwners($token, $processID, $filing->beneficialOwners, $randSeqNum++);

            array_push($parties, ...$owners);



            $xmlData['fc2:Activity']['fc2:Party'] = function () use ($parties) {
                // must add attributes to each party
                return $parties;
            };





            $arrayToXML = new ArrayToXml($xmlData, [
                'rootElementName' => 'fc2:EFilingSubmissionXML',
                '_attributes' => [
                    'xmlns:fc2' => 'www.fincen.gov/base',
                    'xmlns:xsi' => 'http://www.w3.org/2001/XMLSchema-instance',
                    'xsi:schemaLocation' => "www.fincen.gov/base https://www.fincen.gov/sites/default/files/schema/base/BOIRSchema.xsd",
                    'SeqNum' => '1'
                ]
            ], true, 'UTF-8');

            $result = $arrayToXML->prettify()->toXml();


            $this->info('storing XML file');
            Storage::disk('local')->put('public/filings/test.xml', $result);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function generateCompanyInfo($filingCoInfo, $randSeqNum)
    {
        try {
            $this->info('Creating Company Info Section');
            $companyInfo = [
                '_attributes' => [
                    'SeqNum' => $randSeqNum
                ],
                'fc2:ActivityPartyTypeCode' => '62',
                'fc2:Address' => [
                    '_attributes' => ['SeqNum' => $randSeqNum++],
                    'fc2:RawCityText' => $filingCoInfo->current_city,
                    'fc2:RawCountryCodeText' => $filingCoInfo->current_country_code,
                    'fc2:RawStateCodeText' => $filingCoInfo->current_state_code,
                    'fc2:RawStreetAddress1Text' => $filingCoInfo->current_street_address,
                    'fc2:RawZIPCode' => $filingCoInfo->zip,
                ],
                'fc2:FormationCountryCodeText' => $filingCoInfo->country_formation_code,

            ];

            if ($filingCoInfo->existing_reporting_company) {
                $companyInfo['fc2:ExistingReportingCompanyIndicator'] = 'Y';
            }

            if ($filingCoInfo->formation_type == 'foreign') {
                if ($filingCoInfo->tribal_formation_id) {
                    $companyInfo['fc2:FirstRegistrationLocalTribalCodeText'] = $filingCoInfo->tribal_formation_name;
                }
                if ($filingCoInfo->state_formation_id) {
                    $companyInfo['fc2:FirstRegistrationStateCodeText'] = $filingCoInfo->state_formation_code;
                }
            } else {
                if ($filingCoInfo->tribal_formation_id) {
                    $companyInfo['fc2:FormationLocalTribalCodeText'] = $filingCoInfo->tribal_formation_name;
                }
                if ($filingCoInfo->state_formation_id) {
                    $companyInfo['fc2:FormationStateCodeText'] = $filingCoInfo->state_formation_code;
                }
                if ($filingCoInfo->tribal_other_name) {
                    $companyInfo['fc2:OtherFirstRegistrationLocalTribalText'] = 'Other';
                    $companyInfo['fc2:OtherFormationLocalTribalText'] = $filingCoInfo->tribal_other_name;
                }
            }

            if ($filingCoInfo->get_fincen) {
                $companyInfo['fc2:RequestFinCENIDIndicator'] = 'Y';
            }

            $companyInfoPartyNames = [];


            if ($filingCoInfo->alternate_name) {
                // convert string to array
                $alternateNames = json_decode($filingCoInfo->alternate_name);

                foreach ($alternateNames as $name) {
                    array_push($companyInfoPartyNames, [
                        '_attributes' => ['SeqNum' => $randSeqNum++],
                        'fc2:PartyNameTypeCode' => 'DBA',
                        'fc2:RawPartyFullName' => $name
                    ]);
                }
            }
            array_push($companyInfoPartyNames, [
                '_attributes' => ['SeqNum' => $randSeqNum++],
                'fc2:PartyNameTypeCode' => 'L',
                'fc2:RawPartyFullName' => $filingCoInfo->legal_name
            ]);


            $companyInfo += [
                'fc2:PartyName' => function () use ($companyInfoPartyNames) {
                    return $companyInfoPartyNames;
                }
            ];

            $companyInfo['fc2:Address'] += [
                '_attributes' => ['SeqNum' => $randSeqNum++],
                'fc2:RawCityText' => $filingCoInfo->current_city,
                'fc2:RawCountryCodeText' => $filingCoInfo->current_country_code,
                'fc2:RawStateCodeText' => $filingCoInfo->current_state_code,
                'fc2:RawStreetAddress1Text' => $filingCoInfo->current_street_address,
                'fc2:RawZIPCode' => $filingCoInfo->zip,
            ];

            // Container element for Company Tax Identification information

            $PartyIdentificationTypeCode = ($filingCoInfo->tax_id_type_id === 3) ? '9' : (($filingCoInfo->tax_id_type_id === 2) ? '1' : '2');

            $companyInfo['fc2:PartyIdentification'] = [
                '_attributes' => ['SeqNum' => $randSeqNum++],
                'fc2:PartyIdentificationTypeCode' => $PartyIdentificationTypeCode,
                'fc2:PartyIdentificationNumberText' => $filingCoInfo->tax_id_number,
            ];

            if ($filingCoInfo->tax_id_type_id === 3) {
                $companyInfo['fc2:PartyIdentification']['fc2:OtherIssuerCountryText'] = $filingCoInfo->tax_id_country_code;
            }

            if ($filingCoInfo->foreign_pooled_investment) {
                $companyInfo['fc2:OrganizationClassificationTypeSubtype'] = [
                    '_attributes' => ['SeqNum' => $randSeqNum++],
                    'fc2:OrganizationTypeID' => '19'
                ];
            }

            return $companyInfo;
        } catch (\Throwable $th) {
            //throw $th;
            dd("Error in generateCompanyInfo: " . $th->getMessage());
        }
    }

    public function generateCompanyApplicants($token, $processID, $applicants, $randSeqNum)
    {
        try {
            $this->info('Generating Company Applicant XML Section');
            $parties = [];
            foreach ($applicants as $applicant) {

                if ($applicant->fincen_id && Str::length($applicant->fincen_id) === 12) {
                    $addApplicant = [
                        'fc2:ActivityPartyTypeCode' => '63',
                        'fc2:FinCENID' => $applicant->fincen_id,
                        '_attributes' => ['SeqNum' => $randSeqNum++],
                    ];
                    array_push($parties, $addApplicant);
                    continue;
                } else {

                    $this->info('uploading applicant files');
                    $attachmentName = $this->uploadFiles($token, $processID, $applicant);
                    $this->info('completed uploading applicant files');


                    $addApplicant = [
                        '_attributes' => ['SeqNum' => $randSeqNum++],
                        'fc2:ActivityPartyTypeCode' => '63',
                        'fc2:IndividualBirthDateText' => Carbon::parse($applicant->dob)->format('Ymd'),
                        'fc2:PartyName' => [
                            '_attributes' => ['SeqNum' => $randSeqNum++],
                            'fc2:PartyNameTypeCode' => 'L',
                            'fc2:RawEntityIndividualLastName' => $applicant->last_name,
                            'fc2:RawIndividualFirstName' => $applicant->first_name,
                            'fc2:RawIndividualMiddleName' => $applicant->middle_name,
                        ],
                        'fc2:Address' => [
                            '_attributes' => ['SeqNum' => $randSeqNum++],
                            'fc2:RawCityText' => $applicant->city,
                            'fc2:RawCountryCodeText' => $applicant->country_code,
                            'fc2:RawStateCodeText' => $applicant->state_code,
                            'fc2:RawStreetAddress1Text' => $applicant->street_address,
                            'fc2:RawZIPCode' => $applicant->zip,
                        ],

                    ];

                    if ($applicant->suffix) {
                        $addApplicant['fc2:PartyName']['fc2:RawIndividualNameSuffixText'] = $applicant->suffix;
                    }

                    $partyTypeCode = ($applicant->id_type === 'us_passport') ? '39' : (($applicant->id_type === 'foreign_passport') ? '40' : (($applicant->id_type === 'drivers_license') ? '37' : '38'));

                    $addApplicant['fc2:PartyIdentification'] = [
                        '_attributes' => ['SeqNum' => $randSeqNum++],
                        'fc2:PartyIdentificationTypeCode' => $partyTypeCode,
                        'fc2:PartyIdentificationNumberText' => $applicant->id_number,
                        'fc2:OtherIssuerCountryText' => $applicant->id_document_country_code,
                        'fc2:OriginalAttachmentFileName' => $attachmentName,
                    ];

                    if ($applicant->id_type === 'state_tribe_id' && $applicant->id_document_tribe) {
                        $addApplicant['fc2:PartyIdentification']['fc2:IssuerLocalTribalCodeText'] = $applicant->id_document_tribe;
                    }

                    if ($applicant->id_type === 'state_tribe_id' && $applicant->tribal_other_name) {
                        $addApplicant['fc2:PartyIdentification']['fc2:OtherIssuerLocalTribalText'] = $applicant->tribal_other_name;
                    }

                    if ($applicant->id_type === 'state_tribe_id' || $applicant->id_type === 'drivers_license') {
                        $addApplicant['fc2:PartyIdentification']['fc2:OtherIssuerStateText'] = $applicant->id_document_state_code;
                    }
                }




                if ($applicant->address_type === 'residential') {
                    $addApplicant['fc2:Address']['fc2:ResidentialAddressIndicator'] = 'Y';
                } else {
                    $addApplicant['fc2:Address']['fc2:BusinessAddressIndicator'] = 'Y';
                }


                array_push($parties, $addApplicant);
            }

            return $parties;
        } catch (\Throwable $th) {
            //throw $th;
            dd($th->getMessage());
        }
    }

    public function generateBeneficialOwners($token, $processID, $owners, $randSeqNum)
    {
        try {
            $this->info('Generating Beneficial Owner XML Section');
            $parties = [];
            foreach ($owners as $owner) {

                try {
                    if ($owner->fincen_id && Str::length($owner->fincen_id) === 12) {
                        $addOwner = [
                            '_attributes' => ['SeqNum' => $randSeqNum++],
                            'fc2:ActivityPartyTypeCode' => '64',
                            'fc2:FinCENID' => $owner->fincen_id,
                        ];
                        array_push($parties, $addOwner);
                        continue;
                    } elseif ($owner->exempt_entity) {
                        // need partyname => partynameTypeCode, and RawEntityIndividualLastName
                        $addOwner = [
                            '_attributes' => ['SeqNum' => $randSeqNum++],
                            'fc2:ActivityPartyTypeCode' => '64',
                            'fc2:ExemptIndicator' => 'Y',
                            'fc2:PartyName' => [
                                '_attributes' => ['SeqNum' => $randSeqNum++],
                                'fc2:PartyNameTypeCode' => 'L',
                                'fc2:RawEntityIndividualLastName' => $owner->last_name,

                            ],
                        ];
                        array_push($parties, $addOwner);
                        continue;
                    } else {
                        $this->info('uploading Owner files');

                        $attachmentName = $this->uploadFiles($token, $processID, $owner);

                        $this->info('completed uploading Owner files');

                        $partyTypeCode = ($owner->id_type === 'us_passport') ? '39' : (($owner->id_type === 'foreign_passport') ? '40' : (($owner->id_type === 'drivers_license') ? '37' : '38'));

                        $addOwner['fc2:PartyIdentification'] = [
                            '_attributes' => ['SeqNum' => $randSeqNum++],
                            'fc2:PartyIdentificationTypeCode' => $partyTypeCode,
                            'fc2:PartyIdentificationNumberText' => $owner->id_number,
                            'fc2:OtherIssuerCountryText' => $owner->id_document_country_code,
                            'fc2:OriginalAttachmentFileName' => $attachmentName,
                        ];

                        if ($owner->id_type === 'state_tribe_id' && $owner->id_document_tribe) {
                            $addOwner['fc2:PartyIdentification']['fc2:IssuerLocalTribalCodeText'] = $owner->id_document_tribe;
                        }

                        if ($owner->id_type === 'state_tribe_id' && $owner->tribal_other_name) {
                            $addOwner['fc2:PartyIdentification']['fc2:OtherIssuerLocalTribalText'] = $owner->tribal_other_name;
                        }

                        if ($owner->id_type === 'state_tribe_id' || $owner->id_type === 'drivers_license') {
                            $addOwner['fc2:PartyIdentification']['fc2:OtherIssuerStateText'] = $owner->id_document_state_code;
                        }

                        $addOwner = [
                            '_attributes' => ['SeqNum' => $randSeqNum++],
                            'fc2:ActivityPartyTypeCode' => '64',
                            'fc2:IndividualBirthDateText' => Carbon::parse($owner->dob)->format('Ymd'),
                            'fc2:PartyName' => [
                                '_attributes' => ['SeqNum' => $randSeqNum++],
                                'fc2:PartyNameTypeCode' => 'L',
                                'fc2:RawEntityIndividualLastName' => $owner->last_name,
                                'fc2:RawIndividualFirstName' => $owner->first_name,
                                'fc2:RawIndividualMiddleName' => $owner->middle_name,
                            ],
                            'fc2:Address' => [
                                '_attributes' => ['SeqNum' => $randSeqNum++],
                                'fc2:RawCityText' => $owner->city,
                                'fc2:RawCountryCodeText' => $owner->country_code,
                                'fc2:RawStateCodeText' => $owner->state_code,
                                'fc2:RawStreetAddress1Text' => $owner->street_address,
                                'fc2:RawZIPCode' => $owner->zip,
                            ],

                        ];

                        if ($owner->parent_guardian) {
                            $addOwner['fc2:ParentGuardianIndicator'] = 'Y';
                        }

                        if ($owner->suffix) {
                            $addOwner['fc2:PartyName']['fc2:RawIndividualNameSuffixText'] = $owner->suffix;
                        }

                        if ($owner->address_type === 'residential') {
                            $addOwner['fc2:Address']['fc2:ResidentialAddressIndicator'] = 'Y';
                        } else {
                            $addOwner['fc2:Address']['fc2:BusinessAddressIndicator'] = 'Y';
                        }


                        $partyTypeCode = ($owner->id_type === 'us_passport') ? '39' : (($owner->id_type === 'foreign_passport') ? '40' : (($owner->id_type === 'drivers_license') ? '37' : '38'));

                        $addOwner['fc2:PartyIdentification'] = [
                            '_attributes' => ['SeqNum' => $randSeqNum++],
                            'fc2:PartyIdentificationTypeCode' => $partyTypeCode,
                            'fc2:PartyIdentificationNumberText' => $owner->id_number,
                            'fc2:OtherIssuerCountryText' => $owner->id_document_country_code,
                            'fc2:OriginalAttachmentFileName' => $attachmentName,
                        ];

                        if ($owner->id_type === 'state_tribe_id' && $owner->id_document_tribe) {
                            $addApplicant['fc2:PartyIdentification']['fc2:IssuerLocalTribalCodeText'] = $owner->id_document_tribe;
                        }

                        if ($owner->id_type === 'state_tribe_id' && $owner->tribal_other_name) {
                            $addApplicant['fc2:PartyIdentification']['fc2:OtherIssuerLocalTribalText'] = $owner->tribal_other_name;
                        }

                        if ($owner->id_type === 'state_tribe_id' || $owner->id_type === 'drivers_license') {
                            $addApplicant['fc2:PartyIdentification']['fc2:OtherIssuerStateText'] = $owner->id_document_state_code;
                        }
                    }




                    array_push($parties, $addOwner);
                } catch (\Throwable $th) {
                    dd($th->getMessage(), "Owner ID: " . $owner->id);
                }
            }

            return $parties;
        } catch (\Throwable $th) {
            //throw $th;
            dd("Error in generateBeneficialOwners: " . $th->getMessage());
        }
    }


    public function createArray()
    {
        $array = [
            [
                "name" => "filing",
                "element" => "EFilingSubmissionXML",
                "attributes" => [
                    "xmlns:fc2" => "http://www.fincen.gov/base",
                    "xmlns:xsi" => "http://www.w3.org/2001/XMLSchema-instance",
                    "xsi:schemaLocation" => "http://www.fincen.gov/base https://www.fincen.gov/sites/default/files/schema/base/BOIRSchema.xsd",
                    "SeqNum" => "1"
                ],
                "minOccurs" => 1,
                "maxOccurs" => 1,
                "required" => true,
                "children" => [
                    [
                        "element" => "SubmitterElectronicAddressText",
                        "attributes" => [],
                        "minOccurs" => 1,
                        "maxOccurs" => 1,
                        "required" => true,
                        "maxLength" => 500,
                    ],
                    [
                        "element" => "SubmitterEntityIndivdualLastName",
                        "attributes" => [],
                        "minOccurs" => 1,
                        "maxOccurs" => 1,
                        "required" => true,
                        "maxLength" => 150,
                    ],
                    [
                        "element" => "SubmitterIndivdualFirstName",
                        "attributes" => [],
                        "minOccurs" => 1,
                        "maxOccurs" => 1,
                        "required" => true,
                        "maxLength" => 100,
                    ],

                ]
            ],
            [
                "name" => "activity",
                "description" => "Container element for Filing Type information",
                "element" => "Activity",
                "attributes" => ["SeqNum" => ""],
                "minOccurs" => 1,
                "maxOccurs" => 1,
                "required" => true,
                "children" => [
                    [
                        'element' => 'ApprovalOfficialSignatureDateText',
                        'attributes' => [],
                        'minOccurs' => 1,
                        'maxOccurs' => 1,
                        'required' => true,
                        'maxLength' => 10,
                        "format" => "CCYYMMDD",
                        "field" => "item 2",
                        "db_field" => "filings.prepared_date"
                    ],
                    [
                        'element' => 'EFilingPriorReportingCompanyIdentificationNumberText',
                        'attributes' => [],
                        'minOccurs' => 0,
                        'maxOccurs' => 1,
                        'required' => false,
                        'maxLength' => 25,
                        'description' => "Must be 9-numeric characters if EIN or SSN/ITIN, or up to 25 alphanumeric characters if foreign. Record when the type of filing (Item 1b-d) is correct prior report, update prior report, or newly exempt entity.",
                        'field' => 'item 1f',
                        'db_field' => 'company_info.tax_id'
                    ],
                    [
                        'element' => 'EFilingPriorReportingCompanyIdentificationTypeCode',
                        'attributes' => [],
                        'minOccurs' => 0,
                        'maxOccurs' => 1,
                        'required' => false,
                        'description' => "Must equal the single digit tax ID code ('2' EIN, '1' SSN/ITIN, or '9' Foreign). Record when the type of filing (Item 1b-d) is correct prior report, update prior report, or newly exempt entity.",
                        'field' => 'item 1g',
                        'db_field' => 'company_info.tax_id_type'
                    ],
                    [
                        'element' => 'EFilingPriorReportingCompanyIssuerCountryCodeText',
                        'attributes' => [],
                        'minOccurs' => 0,
                        'maxOccurs' => 1,
                        'required' => false,
                        'maxLength' => 2,
                        'description' => "Must equal the foreign ISO-3166 country/jurisdiction code. Record when the Reporting Company tax identification type (Item 1g) is foreign.",
                        'field' => 'item 1h',
                        'db_field' => 'company_info.tax_id_country',
                        'conditional_value' => [
                            'company_info.tax_id_type' => '3'
                        ]
                    ],
                    [
                        'element' => 'EFilingPriorReportingCompanyName',
                        'attributes' => [],
                        'minOccurs' => 0,
                        'maxOccurs' => 1,
                        'required' => false,
                        'maxLength' => 150,
                        'description' => "150 character entry maximum corresponding with item 5 (Reporting Company legal name) of the most recently filed BOIR. Record when the type of filing (Item 1b-d) is correct prior report, update prior report, or newly exempt entity.",
                        'field' => 'item 1e',
                        'db_field' => 'company_info.legal_name'
                    ],
                    [
                        'element' => 'FilingDateText',
                        'attributes' => [],
                        'minOccurs' => 1,
                        'maxOccurs' => 1,
                        'required' => true,
                        'value' => null,
                        'description' => "This element must be recorded. The value must be null."
                    ]
                ]
            ],
            [
                'name' => 'activityAssociation',
                'description' => 'Container element for more filing type information',
                'element' => 'ActivityAssociation',
                'attributes' => ['SeqNum' => ''],
                'minOccurs' => 1,
                'maxOccurs' => 1,
                'required' => true,
                'children' => [
                    [
                        'element' => 'CorrectsAmendsPriorReportIndicator',
                        'attributes' => [],
                        'minOccurs' => 0,
                        'maxOccurs' => 1,
                        'required' => false,
                        'value' => "Y",
                        'description' => "Indicates a correction to the most recently filed BOIR. Include this element when the filing type is a correction.",
                        'conditional_value' => ['filings.filing_type_id' => '2'],
                    ],
                    [
                        'element' => 'InitialReportIndicator',
                        'attributes' => [],
                        'minOccurs' => 0,
                        'maxOccurs' => 1,
                        'required' => false,
                        'value' => "Y",
                        'description' => "Indicates an initial BOIR filing. Include this element when the filing is initial.",
                        'conditional_value' => ['filings.filing_type_id' => '1'],

                    ],
                    [
                        'element' => 'NewlyExemptEntityIndicator',
                        'attributes' => [],
                        'minOccurs' => 0,
                        'maxOccurs' => 1,
                        'required' => false,
                        'value' => "Y",
                        'description' => "Indicates that the reporting entity has become exempt from BOIR filing after the most recent submission. Include this element when the entity is newly exempt.",
                        'conditional_value' => ['filings.filing_type_id' => '4'],

                    ],
                    [
                        'element' => 'UpdatePriorReportIndicator',
                        'attributes' => [],
                        'minOccurs' => 0,
                        'maxOccurs' => 1,
                        'required' => false,
                        'value' => "Y",
                        'description' => "Indicates an update to the most recently filed BOIR. Include this element when the filing updates a prior report.",
                        'conditional_value' => ['filings.filing_type_id' => '3'],

                    ]
                ]
            ],
            [
                "name" => "Company Info",
                "element" => "Party",
                "description" => "Container element for Reporting Company information",
                "attributes" => [
                    "SeqNum" => ""
                ],
                "minOccurs" => 1,
                "maxOccurs" => 1,
                "required" => true,
                "children" => [
                    [
                        "element" => "ActivityPartyTypeCode",
                        "attributes" => [],
                        "minOccurs" => 1,
                        "maxOccurs" => 1,
                        "required" => true,
                        "value" => "62"  // Code associated with the Reporting Company
                    ],
                    [
                        "element" => "ExistingReportingCompanyIndicator",
                        "attributes" => [],
                        "minOccurs" => 0,
                        "maxOccurs" => 1,
                        "required" => false,
                        "value" => "Y",
                        "field" => "item 16",
                        "db_field" => "company_info.existing_reporting_company"
                    ],
                    [
                        "element" => "FirstRegistrationLocalTribalCodeText",
                        "attributes" => [],
                        "minOccurs" => 0,
                        "maxOccurs" => 1,
                        "required" => false,
                        "description" => "",
                        "field" => "item 10f",
                        "db_field" => "company_info.tribal_formation_id",
                        "conditional_value" => [
                            "company_info.formation_type" => "foreign"
                        ]

                    ],
                    [
                        "element" => "FirstRegistrationStateCodeText",
                        "attributes" => [],
                        "minOccurs" => 0,
                        "maxOccurs" => 1,
                        "required" => false,
                        "field" => "item 10e",
                        "db_field" => "company_info.state_formation_id",
                        "conditional_value" => [
                            "company_info.formation_type" => "foreign"
                        ]
                    ],
                    [
                        "element" => "FormationCountryCodeText",
                        "attributes" => [],
                        "minOccurs" => 1,
                        "maxOccurs" => 1,
                        "required" => true,
                        "value" => "",  // Placeholder for dynamic input
                        "field" => "item 10a",
                        "db_field" => "company_info.country_formation_id"
                    ],
                    [
                        "element" => "FormationLocalTribalCodeText",
                        "attributes" => [],
                        "minOccurs" => 0,
                        "maxOccurs" => 1,
                        "required" => false,
                        "field" => "item 10c",
                        "db_field" => "company_info.tribal_formation_id"

                    ],
                    [
                        "element" => "FormationStateCodeText",
                        "attributes" => [],
                        "minOccurs" => 0,
                        "maxOccurs" => 1,
                        "required" => false,
                        "field" => "item 10b",
                        "db_field" => "company_info.state_formation_id"

                    ],
                    [
                        "element" => "OtherFirstRegistrationLocalTribalText",
                        "attributes" => [],
                        "minOccurs" => 0,
                        "maxOccurs" => 1,
                        "required" => false,
                        "field" => "item 10g",
                        "db_field" => "company_info.tribal_other_name",
                        "conditional_value" => [
                            "company_info.formation_type" => "foreign"
                        ]
                    ],
                    [
                        "element" => "OtherFormationLocalTribalText",
                        "attributes" => [],
                        "minOccurs" => 0,
                        "maxOccurs" => 1,
                        "required" => false,
                        "field" => "item 10d",
                        "db_field" => "company_info.tribal_other_name"
                    ],
                    [
                        "element" => "RequestFinCENIDIndicator",
                        "attributes" => [],
                        "minOccurs" => 0,
                        "maxOccurs" => 1,
                        "required" => false,
                        "value" => "Y",
                        "field" => "item 3",
                        "db_field" => "company_info.get_fincen"
                    ],
                    [
                        "name" => "Company Legal name",
                        "element" => "PartyName",
                        "attributes" => ["SeqNum" => ""],  // Adjust the unique number accordingly
                        "minOccurs" => 1,
                        "maxOccurs" => 1,
                        "required" => true,
                        "children" => [
                            [
                                "element" => "PartyNameTypeCode",
                                "attributes" => [],
                                "minOccurs" => 1,
                                "maxOccurs" => 1,
                                "required" => true,
                                "value" => "L"  // Legal name code
                            ],
                            [
                                "element" => "RawPartyFullName",
                                "attributes" => [],
                                "minOccurs" => 1,
                                "maxOccurs" => 1,
                                "required" => true,
                                "value" => "",  // Placeholder for dynamic input
                                "field" => "item 5",
                                'db_field' => 'company_info.legal_name'
                            ]
                        ]
                    ],
                    // foreach company_info.alternate_name as $name
                    [
                        "name" => "Company Alternate names",
                        "element" => "PartyName",
                        "attributes" => ["SeqNum" => ""],  // Adjust the unique number accordingly
                        "minOccurs" => 0,
                        "maxOccurs" => 1,
                        "required" => true,
                        "children" => [
                            [
                                "element" => "PartyNameTypeCode",
                                "attributes" => [],
                                "minOccurs" => 1,
                                "maxOccurs" => 1,
                                "required" => true,
                                "value" => "DBA"
                            ],
                            [
                                "element" => "RawPartyFullName",
                                "attributes" => [],
                                "minOccurs" => 1,
                                "maxOccurs" => 1,
                                "required" => true,
                                "value" => "",  // Placeholder for dynamic input
                                "field" => "item 6",
                                'db_field' => 'company_info.alternate_name'
                            ]
                        ],
                        "description" => "This element is only required for each alternate name. The PartyNameTypeCode must be blank."
                    ],
                    [
                        "element" => "Address",
                        "attributes" => ["SeqNum" => ""],  // Adjust the unique number accordingly
                        "minOccurs" => 1,
                        "maxOccurs" => 1,
                        "required" => true,
                        "children" => [
                            [
                                "element" => "RawCityText",
                                "attributes" => [],
                                "minOccurs" => 1,
                                "maxOccurs" => 1,
                                "required" => true,
                                'field' => 'item 12',
                                'db_field' => 'company_info.current_city'
                            ],
                            [
                                "element" => "RawCountryCodeText",
                                "attributes" => [],
                                "minOccurs" => 1,
                                "maxOccurs" => 1,
                                "required" => true,
                                "format" => "ISO-3166",
                                'field' => 'item 13',
                                'db_field' => 'company_info.current_country_id'
                            ],
                            [
                                "element" => "RawStateCodeText",
                                "attributes" => [],
                                "minOccurs" => 1,
                                "maxOccurs" => 1,
                                "required" => true,
                                "format" => "ISO-3166",
                                'field' => 'item 14',
                                'db_field' => 'company_info.current_state_id'
                            ],
                            [
                                "element" => "RawStreetAddress1Text",
                                "attributes" => [],
                                "minOccurs" => 1,
                                "maxOccurs" => 1,
                                "required" => true,
                                'field' => 'item 11',
                                'db_field' => 'company_info.current_street_address'
                            ],
                            [
                                'element' => "RawZIPCode",
                                'attributes' => [],
                                'minOccurs' => 1,
                                'maxOccurs' => 1,
                                'required' => true,
                                'field' => 'item 15',
                                'db_field' => 'company_info.zip'
                            ]
                        ]
                    ],
                    [
                        'element' => 'PartyIdentification',
                        'description' => 'Container element for Reporting Company tax identification information',
                        'attributes' => [
                            "SeqNum" => ""  // Adjust the unique number accordingly
                        ],
                        'minOccurs' => 1,
                        'maxOccurs' => 1,
                        'required' => true,
                        'children' => [
                            [
                                'element' => 'PartyIdentificationTypeCode',
                                'attributes' => [],
                                'minOccurs' => 1,
                                'maxOccurs' => 1,
                                'required' => true,
                                'description' => "1 for SSn/ITIN, 2 for EIN, 9 for Foreign",
                                'value' => "",  // Placeholder for dynamic input
                                'field' => 'item 7',
                                'db_field' => 'company_info.tax_id_type'
                            ],
                            [
                                'element' => 'PartyIdentificationNumberText',
                                'attributes' => [],
                                'minOccurs' => 1,
                                'maxOccurs' => 1,
                                'required' => true,
                                'value' => "",  // Placeholder for dynamic input
                                'field' => 'item 8',
                                'db_field' => 'company_info.tax_id_number'
                            ],
                            [
                                'element' => 'OtherIssuerCountryText',
                                'description' => 'only use if the tax identification type is foreign',
                                'attributes' => [],
                                'minOccurs' => 0,
                                'maxOccurs' => 1,
                                'depends_on' => 'PartyIdentificationTypeCode',
                                'field' => 'item 9',
                                'db_field' => 'company_info.tax_id_country_id',
                                'conditional_value' => [
                                    'company_info.tax_id_type' => '3'
                                ]
                            ]
                        ]
                    ],
                    [
                        'element' => 'OrganizationClassificationTypeSubtype',
                        'attributes' => [],
                        'minOccurs' => 0,
                        'maxOccurs' => 1,
                        'required' => false,
                        'description' => "This is the container element for indicating that the Reporting Company is a foreign pooled investment vehicle.",
                        'children' => [
                            [
                                'element' => 'OrganizationTypeID',
                                'attributes' => [],
                                'minOccurs' => 1,
                                'maxOccurs' => 1,
                                'required' => true,
                                'value' => "19",
                                'field' => 'item 4',
                                'db_field' => 'company_info.foreign_pooled_investment'
                            ]
                        ]
                    ]
                ]
            ],
            [
                'name' => 'companyApplicant',
                'description' => 'Container element for Company Applicant information',
                'element' => 'Party',
                'value' => '63',
                'attributes' => [
                    'SeqNum' => ''
                ],
                'minOccurs' => 0,
                'maxOccurs' => 99,
                'required' => false,
                'children' => [
                    [
                        'element' => 'ActivityPartyTypeCode',
                        'attributes' => [],
                        'minOccurs' => 1,
                        'maxOccurs' => 1,
                        'required' => true,
                        'value' => '63'
                    ],
                    [
                        'element' => 'FinCENID',
                        'attributes' => [],
                        'minOccurs' => 0,
                        'maxOccurs' => 1,
                        'required' => false,
                        'description' => 'if provided, no other fields should be provided'
                    ],
                    [
                        'element' => 'IndividualBirthDateText',
                        'attributes' => [],
                        'minOccurs' => 0,
                        'maxOccurs' => 1,
                        'format' => 'CCYYMMDD',
                    ],
                    [

                        'element' => 'PartyName',
                        'attributes' => [
                            'SeqNum' => ''
                        ],
                        'minOccurs' => 0,
                        'maxOccurs' => 1,
                        "children" => [
                            [
                                "element" => "PartyNameTypeCode",
                                "attributes" => [],
                                "minOccurs" => 1,
                                "maxOccurs" => 1,
                                "required" => true,
                                "value" => "L"  // Legal name code
                            ],
                            [
                                "element" => "RawEntityIndividualLastName",
                                "attributes" => [],
                                "minOccurs" => 1,
                                "maxOccurs" => 1,
                                "required" => true,
                                "value" => ""  // Placeholder for dynamic input
                            ],
                            [
                                "element" => "RawIndividualFirstName",
                                "attributes" => [],
                                "minOccurs" => 1,
                                "maxOccurs" => 1,
                                "required" => true,
                                "value" => ""  // Placeholder for dynamic input
                            ],
                            [
                                "element" => "RawIndividualMiddleName",
                                "attributes" => [],
                                "minOccurs" => 1,
                                "maxOccurs" => 1,
                                "required" => true,
                                "value" => ""  // Placeholder for dynamic input
                            ],
                            [
                                "element" => "RawIndividualNameSuffixText",
                                "attributes" => [],
                                "minOccurs" => 1,
                                "maxOccurs" => 1,
                                "required" => true,
                                "value" => ""  // Placeholder for dynamic input
                            ]

                        ]
                    ],
                    [
                        'element' => 'Address',
                        'attributes' => [
                            'SeqNum' => ''
                        ],
                        'minOccurs' => 0,
                        'maxOccurs' => 1,
                        'description' => 'record when there is no FinCEN',
                        'children' => [
                            [
                                'element' => 'BusinessAddressIndicator',
                                'attributes' => [],
                                'minOccurs' => 0,
                                'maxOccurs' => 1,
                                'value' => 'Y'
                            ],
                            [
                                'element' => 'RawCityText',
                            ],
                            [
                                'element' => 'RawCountryCodeText',
                            ],
                            [
                                'element' => 'RawStateCodeText',
                            ],
                            [
                                'element' => 'RawStreetAddress1Text',
                            ],
                            [
                                'element' => 'RawZIPCode',
                            ],
                            [
                                'element' => 'ResidentialAddressIndicator',
                                'value' => 'Y'
                            ]
                        ]
                    ],
                    [
                        'element' => 'PartyIdentification',
                        'attributes' => [
                            'SeqNum' => ''
                        ],
                        'minOccurs' => 0,
                        'maxOccurs' => 1,
                        'description' => 'container element for Company Applicants form of identification',
                        'children' => [
                            [
                                'element' => 'IssuerLocalTribalCodeText',
                                'minOccurs' => 0,
                                'maxOccurs' => 1,

                            ],
                            [
                                'element' => 'OriginalAttachmentFileName',
                                'minOccurs' => 0,
                                'maxOccurs' => 1,

                            ],
                            [
                                'element' => 'OtherIssuerCountryText',
                                'minOccurs' => 0,
                                'maxOccurs' => 1,

                            ],
                            [
                                'element' => 'OtherIssuerLocalTribalText',
                                'minOccurs' => 0,
                                'maxOccurs' => 1,

                            ],
                            [
                                'element' => 'OtherIssuerStateText',
                                'minOccurs' => 0,
                                'maxOccurs' => 1,

                            ],
                            [
                                'element' => 'PartyIdentificationNumberText',
                                'minOccurs' => 0,
                                'maxOccurs' => 1,

                            ],
                            [
                                'element' => 'PartyIdentificationTypeCode',
                                'minOccurs' => 0,
                                'maxOccurs' => 1,
                                'description' => "Must equal 37 (State issued driver's license), 38 (State/Local/Tribe issued ID), 39 (US Passport), or 40 (Foreign Passport)."

                            ],

                        ]
                    ],
                ],

            ],
            [
                'name' => 'beneficialOwner',
                'description' => 'Container element for Beneficial Owner information',
                'element' => 'Party',
                'attributes' => [
                    'SeqNum' => ''
                ],
                'minOccurs' => 0,
                'maxOccurs' => 99,
                'required' => false,
                'children' => [
                    [
                        'element' => 'ActivityPartyTypeCode',
                        'attributes' => [],
                        'minOccurs' => 1,
                        'maxOccurs' => 1,
                        'required' => true,
                        'value' => '64',
                    ],
                    [
                        'element' => 'ExemptIndicator',
                        'attributes' => [],
                        'minOccurs' => 0,
                        'maxOccurs' => 1,
                        'required' => false,
                        'value' => 'Y',
                        'field' => 'item 37',
                        'db_field' => 'beneficial_owners.exempt_entity',
                        'conditional_value' => [
                            'beneficial_owners.exempt_entity' => true
                        ]
                    ],
                    [
                        'element' => 'FinCENID',
                        'attributes' => [],
                        'minOccurs' => 0,
                        'maxOccurs' => 1,
                        'required' => false,
                        'description' => 'if provided, no other fields should be provided',
                        'field' => 'item 36',
                        'db_field' => 'beneficial_owners.fincen_id'
                    ],
                    [
                        'element' => 'IndividualBirthDateText',
                        'attributes' => [],
                        'minOccurs' => 0,
                        'maxOccurs' => 1,
                        'format' => 'CCYYMMDD',
                        'field' => 'item 42',
                        'db_field' => 'beneficial_owners.dob'
                    ],
                    [
                        'element' => 'ParentOrLegalGuardianForMinorChildIndicator',
                        'attributes' => [],
                        'minOccurs' => 0,
                        'maxOccurs' => 1,
                        'vale' => 'Y',
                        'field' => 'item 35',
                        'db_field' => 'beneficial_owners.parent_guardian'
                    ],
                    [

                        'element' => 'PartyName',
                        'attributes' => [
                            'SeqNum' => ''
                        ],
                        'minOccurs' => 0,
                        'maxOccurs' => 1,
                        "children" => [
                            [
                                "element" => "PartyNameTypeCode",
                                "attributes" => [],
                                "minOccurs" => 1,
                                "maxOccurs" => 1,
                                "required" => true,
                                "value" => "L",  // Legal name code
                            ],
                            [
                                "element" => "RawEntityIndividualLastName",
                                "attributes" => [],
                                "minOccurs" => 1,
                                "maxOccurs" => 1,
                                "required" => true,
                                "field" => "item 38",
                                'db_field' => 'beneficial_owners.last_name'
                            ],
                            [
                                "element" => "RawIndividualFirstName",
                                "attributes" => [],
                                "minOccurs" => 1,
                                "maxOccurs" => 1,
                                "required" => true,
                                "field" => "item 39",
                                'db_field' => 'beneficial_owners.first_name'
                            ],
                            [
                                "element" => "RawIndividualMiddleName",
                                "attributes" => [],
                                "minOccurs" => 0,
                                "maxOccurs" => 1,
                                "required" => false,
                                "field" => "item 40",
                                'db_field' => 'beneficial_owners.middle_name'
                            ],
                            [
                                "element" => "RawIndividualNameSuffixText",
                                "attributes" => [],
                                "minOccurs" => 0,
                                "maxOccurs" => 1,
                                "required" => false,
                                "field" => ""
                            ]
                        ]
                    ],
                    [
                        'element' => 'Address',
                        'attributes' => [
                            'SeqNum' => ''
                        ],
                        'minOccurs' => 0,
                        'maxOccurs' => 1,
                        'description' => 'record when there is no FinCEN',
                        'children' => [
                            [
                                'element' => 'RawCityText',
                                'field' => 'item 44',
                                'db_field' => 'beneficial_owners.city'
                            ],
                            [
                                'element' => 'RawCountryCodeText',
                                'field' => 'item 45',
                                'db_field' => 'beneficial_owners.country_id'
                            ],
                            [
                                'element' => 'RawStateCodeText',
                                'field' => 'item 46',
                                'db_field' => 'beneficial_owners.state_id'
                            ],
                            [
                                'element' => 'RawStreetAddress1Text',
                                'field' => 'item 43',
                                'db_field' => 'beneficial_owners.address'
                            ],
                            [
                                'element' => 'RawZIPCode',
                                'field' => 'item 47',
                                'db_field' => 'beneficial_owners.zip'
                            ],

                        ]
                    ],
                    [
                        'element' => "PartyIdentification",
                        'attributes' => [
                            'SeqNum' => ''
                        ],
                        'minOccurs' => 0,
                        'maxOccurs' => 1,
                        'description' => 'container element for Beneficial Owner form of identification',
                        'children' => [
                            [
                                'element' => 'IssuerLocalTribalCodeText',
                                'minOccurs' => 0,
                                'maxOccurs' => 1,
                                'field' => 'item 50c',
                                'db_field' => 'beneficial_owners.id_document_tribe'
                            ],
                            [
                                'element' => 'OriginalAttachmentFileName',
                                'minOccurs' => 0,
                                'maxOccurs' => 1,
                                'field' => 'item 51',
                                'db_field' => 'beneficial_owners.id_document_file_id.name'
                            ],
                            [
                                'element' => 'OtherIssuerCountryText',
                                'minOccurs' => 0,
                                'maxOccurs' => 1,
                                'field' => 'item 50a',
                                'db_field' => 'beneficial_owners.id_document_country_id'
                            ],
                            [
                                'element' => 'OtherIssuerLocalTribalText',
                                'minOccurs' => 0,
                                'maxOccurs' => 1,
                                'field' => 'item 50d',
                                'db_field' => 'beneficial_owners.tribal_other_name'
                            ],
                            [
                                'element' => 'OtherIssuerStateText',
                                'minOccurs' => 0,
                                'maxOccurs' => 1,
                                'field' => 'item 50b',
                                'db_field' => 'beneficial_owners.id_document_state',
                                'conditional_value' => [
                                    'beneficial_owners.id_type' => 'state_tribe_id',
                                    'beneficial_owners.id_type' => 'driver_license',
                                ]
                            ],
                            [
                                'element' => 'PartyIdentificationNumberText',
                                'minOccurs' => 1,
                                'maxOccurs' => 1,
                                'field' => 'item 49',
                                'db_field' => 'beneficial_owners.id_number'
                            ],
                            [
                                'element' => 'PartyIdentificationTypeCode',
                                'minOccurs' => 1,
                                'maxOccurs' => 1,
                                'description' => "Must equal 37 (State issued driver's license), 38 (State/Local/Tribe issued ID), 39 (US Passport), or 40 (Foreign Passport).",
                                'field' => 'item 48',
                                'db_field' => 'beneficial_owners.id_type'

                            ],
                        ]
                    ]
                ],

            ]
        ];
    }
}



// The following is an overview of the BOIR filing processing via API:
// 1. Initiate BOIR Submission via /processId: To initiate the BOIR filing process, the
// user’s system sends a GET request to the /processId resource, resulting in a process ID
// returned (e.g., “BOIR230921650c447660”).
// 2. Upload Identifying Document Image(s) via /attachments: User’s system uploads
// the identifying document image for each Company Applicant and Beneficial Owner
// reported in the BOIR via the /attachments resource, resulting in an upload status
// returned (e.g., “upload_success”). If no Company Applicant and/or Beneficial Owner
// are being reported in the BOIR, then skip this step.
// NOTE: The filenames for each image attachment in the BOIR must be
// unique and referenced in the associated BOIR XML under the element
// “OriginalAttachmentFileName” for each Company Applicant and Beneficial Owner
// being reported.
// 3. Upload BOIR XML via /upload: User’s system uploads a single BOIR as an XML file
// binary via the /upload resource, returning a submission status for the process ID (e.g.,
// “submission_initiated”)
// 4. Track Submission Status via /submissionStatus: User’s system begins querying
// the status of the submission via the /submissionStatus resource (e.g., “submission_
// accepted”).
// Retrieve the BOIR Transcript via /transcript: If a status of “submission_accepted”
// or “submission_rejected” is returned, filer’s system retrieves the transcript PDF of
// the BOIR as binary data via the /transcript resource. The /transcript resource also
// produces the status of the submission.
// NOTE: The binary data for the transcript is base64-encoded. As an example, in
// JavaScript, to create the PDF file, the binary data will need to be decoded from
// base64 and then downloaded as a blob with the mime-type “application/pdf”


// XML Info:
// EFilingPriorReportingCompanyIdentificationNumberText - TAXID number for the reporting company. Must be the same as <PartyIdentificationNumberText> Record this element when the type of filing (Item 1b-d) is correct prior report, update prior report, or newly exempt entity.
// EFilingPriorReportingCompanyIdentificationTypeCode - 1 for SSn/ITIN, 2 for EIN, 9 for Foreign Record this element when the type of filing (Item 1b-d) is correct prior report, update prior report, or newly exempt entity.
// EFilingPriorReportingCompanyIssuerCountryCodeText Record this element when the Reporting Company tax identification type (Item 1g) is foreign. Must equal the foreign ISO-3166 country
// EFilingPriorReportingCompanyName - Record this element when the type of filing (Item 1b-d) is correct prior report, update prior report, or newly exempt entity. 150 character entry maximum corresponding with item 5
// Party - required unless Newly Exempt Entity
