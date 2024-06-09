import { useState } from "react";
import { useForm } from '@inertiajs/react';
import AuthSidebarLayout from "@/Layouts/AuthSidebarLayout";
import Alert from "@/Components/Alert";
import { Checkbox, Input, Select, Button, Steps } from "react-daisyui"

export default function CompanyInformation({ auth, companyInfo, filingTypes, status }) {
    const taxIDTypes = [
        { value: 'EIN', label: 'EIN' },
        { value: 'SSN/TIN', label: 'SSN/TIN' },
        { value: 'Foreign', label: 'Foreign' },
    ];

    const [selectedTaxIDType, setSelectedTaxIDType] = useState('');
    const [selectedCountry, setSelectedCountry] = useState('');
    const [selectedState, setSelectedState] = useState('');
    const [selectedTribal, setSelectedTribal] = useState('');
    const [isForeignPooledInvestment, setIsForeignPooledInvestment] = useState({
        value: false,
        label: 'Foreign Pooled Investment Vehicle',
        message: ''
    });

    // TODO: if all required fields are not filled out, disable the save button
    const [isDisabled, setIsDisabled] = useState(false);
    // TODO: handle existing_reporting_company logic here

    const { data, setData, post, processing, errors, reset } = useForm({
        get_fincen: companyInfo?.get_fincen,
        foreign_pooled_investment: companyInfo?.foreign_pooled_investment,
        existing_reporting_company: companyInfo?.existing_reporting_company,
        filing_id: companyInfo?.filing_id,
        legal_name: companyInfo?.legal_name,
        alternate_name: companyInfo?.alternate_name || [],
        tax_id_type_id: companyInfo?.tax_id_type_id,
        tax_id_number: companyInfo?.tax_id_number,
        tax_id_country_id: companyInfo?.tax_id_country_id,
        formation_type: companyInfo?.formation_type,
        country_formation_id: companyInfo?.country_formation_id,
        state_formation_id: companyInfo?.state_formation_id,
        tribal_formation_id: companyInfo?.tribal_formation_id,
        tribal_other_name: companyInfo?.tribal_other_name,
        current_street_address: companyInfo?.current_street_address,
        current_city: companyInfo?.current_city,
        current_state_id: companyInfo?.current_state_id,
        current_country_id: companyInfo?.current_country_id,
        zip: companyInfo?.zip,
    });


    const handleForeignPooledInvestment = (e) => {
        // handle logic here
        setIsForeignPooledInvestment({
            ...isForeignPooledInvestment,
            value: e.target.checked,
            message: e.target.checked ? 'Company Applicant section will be skipped and only 1 beneficial owner will be required' : ''
        });

    }

    console.log(selectedTaxIDType);
    return (
        <AuthSidebarLayout
            user={auth.user}
        >
            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                    <div className="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                        <Steps
                            className="w-full steps"
                            vertical={false}
                        >
                            <Steps.Step color="primary">Filing Info</Steps.Step>
                            <Steps.Step color="primary">Company Information</Steps.Step>
                            <Steps.Step>Company Applicants</Steps.Step>
                            <Steps.Step >Beneficial Owners</Steps.Step>
                            <Steps.Step >Review & Submit</Steps.Step>
                        </Steps>
                    </div>
                    {isForeignPooledInvestment.value && (
                        <Alert className="mb-4" message={isForeignPooledInvestment.message} type="warning" />
                    )}
                    <div className="p-4 sm:p-8 bg-white shadow sm:rounded-lg">

                        <h2 className="font-semibold text-2xl text-gray-800 leading-tight">Company Information</h2>
                        <form>
                            <div className="component-preview p-4 font-sans">
                                <div className="grid gap-4 grid-cols-2 mb-6 items-center" >
                                    <label className="label w-full">Select to request to receive FinCEN Identifier (FinCEN ID)</label>
                                    <Checkbox
                                        name="get_fincen"
                                        size="lg"
                                        className="checkbox checkbox-secondary" />

                                </div>
                                <div className="grid gap-4 grid-cols-2 mb-6 items-center" >
                                    <label className="label w-full">Foreign Pooled Investment Vehicle?</label>
                                    <Checkbox
                                        name="foreign_pooled_investment"
                                        size="lg"
                                        className="checkbox checkbox-secondary"
                                        onChange={handleForeignPooledInvestment}
                                    />

                                </div>
                                <div className="grid gap-4 grid-cols-2 mb-6 items-center" >
                                    <label className="label w-full"><span>Existing reporting company (Check if Reporting Company was created or registered <strong> <em>before</em> </strong> January 1, 2024)</span></label>
                                    <Checkbox
                                        name="existing_reporting_company"
                                        size="lg"
                                        className="checkbox checkbox-secondary"

                                    />

                                </div>
                                <div className="w-full border-b-2 mb-2">
                                    <h3 className="font-semibold text-lg text-gray-800 leading-tight">Full Legal name and alternate names</h3>
                                </div>
                                <div className="grid gap-4 grid-cols-2 mb-6">
                                    <label className="label">Reporting Company Legal Name</label>
                                    <Input name="legal_name" className="max-w-md input input-bordered input-secondary" bordered={true} placeholder="Entity Legal Name" />
                                </div>
                                <div className="grid gap-4 grid-cols-2 mb-6">
                                    {/* TODO: add ability to enter multiple alternate names */}
                                    <label className="label">Alternate Name (e.g. trade name, DBA)</label>
                                    <Input name="alternate_name[]" className="max-w-md input input-bordered input-secondary" bordered={true} placeholder="Entity Altername Name" />
                                </div>
                                <div className="w-full border-b-2 mb-2">
                                    <h3 className="font-semibold text-lg text-gray-800 leading-tight">Form of Identification</h3>
                                </div>
                                <div className="grid gap-4 grid-cols-2 mb-6">
                                    <label className="label">Select Tax Identification Type</label>
                                    <Select
                                        name="tax_id_type"
                                        className="w-full max-w-md select select-secondary"
                                        defaultValue={'default'}
                                        onChange={(e) => setSelectedTaxIDType(e.target.value)}
                                    >
                                        <Select.Option value={'default'} disabled>
                                            Pick your Tax Identification Type
                                        </Select.Option>
                                        <Select.Option value={''}></Select.Option>
                                        <Select.Option value={'EIN'}>EIN</Select.Option>
                                        <Select.Option value={'SSN/TIN'}>SSN/TIN</Select.Option>
                                        <Select.Option value={'Foreign'}>Foreign</Select.Option>
                                    </Select>
                                </div>
                                <div className="grid gap-4 grid-cols-2 mb-6">
                                    <label className="label">Tax ID Number</label>
                                    <Input name="tax_id_number" className="max-w-md input input-bordered input-secondary" placeholder="Tax ID Number" />
                                </div>
                                <div className="grid gap-4 grid-cols-2 mb-6">
                                    <label className="label">Country/Jurisdiction (if foreign tax ID only)</label>
                                    <Select
                                        name="tax_id_type"
                                        className={`w-full max-w-md select select-secondary`}
                                        defaultValue={'default'}
                                        disabled={selectedTaxIDType !== 'Foreign'}
                                    >
                                        <Select.Option value={'default'} disabled>
                                            Select your country
                                        </Select.Option>
                                        <Select.Option value={''}></Select.Option>
                                        <Select.Option value={'USA'}>USA</Select.Option>
                                        <Select.Option value={'Canada'}>Canada</Select.Option>
                                        <Select.Option value={'Mexico'}>Mexico</Select.Option>
                                        <Select.Option value={'UK'}>UK</Select.Option>
                                        <Select.Option value={'Germany'}>Germany</Select.Option>
                                        <Select.Option value={'France'}>France</Select.Option>

                                    </Select>
                                </div>
                                <div className="w-full border-b-2 mb-2">
                                    <h3 className="font-semibold text-lg text-gray-800 leading-tight">Jurisdiction of formation of first registration</h3>
                                </div>
                                <div className="grid gap-4 grid-cols-2 mb-6">
                                    <label className="label">Country/Jurisdiction of formation</label>
                                    <Select
                                        name="country_formation"
                                        className="w-full max-w-md select select-secondary"
                                        defaultValue={'default'}
                                        onChange={(e) => setSelectedCountry(e.target.value)}
                                    >
                                        <Select.Option value={'default'} disabled>
                                            Select the Country of Formation
                                        </Select.Option>
                                        <Select.Option value={''}></Select.Option>
                                        <Select.Option value={'USA'}>USA</Select.Option>
                                        <Select.Option value={'Canada'}>Canada</Select.Option>
                                        <Select.Option value={'Mexico'}>Mexico</Select.Option>
                                        <Select.Option value={'UK'}>UK</Select.Option>
                                        <Select.Option value={'Germany'}>Germany</Select.Option>
                                        <Select.Option value={'France'}>France</Select.Option>
                                    </Select>
                                </div>
                                {/* //TODO: if country other than USA (including puerto rico, samao, etc) then display different fields below */}
                                {/* TODO: add helpers that show required fields and tooltips */}
                                <div className="grid gap-4 grid-cols-2 mb-6">
                                    <label className="label">State of Formation</label>
                                    <Select
                                        name="state_formation"
                                        className={`w-full max-w-md select select-secondary`}
                                        defaultValue={'default'}
                                        onChange={(e) => setSelectedState(e.target.value)}
                                        disabled={selectedCountry !== 'USA' || selectedTribal !== ''}
                                    >
                                        <Select.Option value={'default'} disabled>
                                            Select the State of Formation
                                        </Select.Option>
                                        <Select.Option value={''}></Select.Option>
                                        <Select.Option value={'AL'}>Alabama</Select.Option>
                                        <Select.Option value={'AK'}>Alaska</Select.Option>
                                        <Select.Option value={'AZ'}>Arizona</Select.Option>
                                    </Select>
                                </div>
                                <div className="grid gap-4 grid-cols-2 mb-6">
                                    <label className="label">Tribal Jurisdiction of formation</label>
                                    <Select
                                        name="tribal_formation"
                                        className={`w-full max-w-md select select-secondary`}
                                        defaultValue={'default'}
                                        onChange={(e) => setSelectedTribal(e.target.value)}
                                        disabled={selectedCountry !== 'USA' || selectedState !== ''}
                                    >
                                        <Select.Option value={'default'} disabled>
                                            Select the Tribal Jurisdiction of Formation
                                        </Select.Option>
                                        <Select.Option value={''}></Select.Option>
                                        <Select.Option value={'Navajo'}>Navajo</Select.Option>
                                        <Select.Option value={'Cherokee'}>Cherokee</Select.Option>
                                        <Select.Option value={'Sioux'}>Sioux</Select.Option>
                                        <Select.Option value={'Other'}>Other</Select.Option>
                                    </Select>
                                </div>
                                {/* TODO: if selection above is Other, then display this field */}
                                <div className={`grid gap-4 grid-cols-2 mb-6 ${selectedTribal !== "Other" ? 'hidden' : ''}`}>
                                    <label className="label">Name of the other Tribe</label>
                                    <Input name="tribal_other_name" className={`max-w-md input input-bordered input-secondary`} placeholder="Name of the other Tribe" />
                                </div>

                                <div className="w-full border-b-2 mb-2">
                                    <h3 className="font-semibold text-lg text-gray-800 leading-tight">Current U.S. Address</h3>
                                </div>
                                <div className="grid gap-4 grid-cols-2 mb-6">
                                    <label className="label">Street Address</label>
                                    <Input name="street_address" className="max-w-md input input-bordered input-secondary" placeholder="Street Address" />
                                </div>
                                <div className="grid gap-4 grid-cols-2 mb-6">
                                    <label className="label">City</label>
                                    <Input name="city" className="max-w-md input input-bordered input-secondary" placeholder="City" />
                                </div>
                                <div className="grid gap-4 grid-cols-2 mb-6">
                                    <label className="label">U.S. or U.S. Territory</label>
                                    <Select name="state" className="w-full max-w-md select select-secondary" defaultValue={'default'}>
                                        <Select.Option value={'default'} disabled>
                                            U.S. or U.S. Territory
                                        </Select.Option>
                                        <Select.Option value={'USA'}>USA</Select.Option>
                                        <Select.Option value={'Puerto Rico'}>Puerto Rico</Select.Option>
                                        <Select.Option value={'Guam'}>Guam</Select.Option>
                                        <Select.Option value={'American Samoa'}>American Samoa</Select.Option>
                                        <Select.Option value={'US Virgin Islands'}>US Virgin Islands</Select.Option>
                                    </Select>
                                </div>

                                <div className="grid gap-4 grid-cols-2 mb-6">
                                    <label className="label">State</label>
                                    <Select name="state" className="w-full max-w-md select select-secondary" defaultValue={'default'}>
                                        <Select.Option value={'default'} disabled>
                                            Select the State
                                        </Select.Option>
                                        <Select.Option value={'AL'}>Alabama</Select.Option>
                                        <Select.Option value={'AK'}>Alaska</Select.Option>
                                        <Select.Option value={'AZ'}>Arizona</Select.Option>
                                    </Select>
                                </div>
                                <div className="grid gap-4 grid-cols-2 mb-6">
                                    <label className="label">ZIP</label>
                                    <Input name="zip" className="max-w-md input input-bordered input-secondary" placeholder="ZIP" />
                                </div>

                                <div className="flex justify-between">
                                    <Button color="secondary" className="font-bold text-white btn btn-secondary" disabled={isDisabled}>Cancel</Button>
                                    <Button
                                        color="primary"
                                        className="btn btn-primary font-bold text-white"
                                        disabled={isDisabled}
                                        // these values will change and redirect will happen at controller
                                        tag="a"
                                        href={route('filing.applicants', { filing_id: 1 })}
                                    >Save & Continue </Button>
                                </div>

                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </AuthSidebarLayout>
    )
}