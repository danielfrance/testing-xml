import { useEffect, useState } from "react";
import { useForm } from '@inertiajs/react';
import AuthSidebarLayout from "@/Layouts/AuthSidebarLayout";
import { Button, Input, Select, Steps  } from "react-daisyui"
import SearchableSelect from "@/Components/SearchableSelect";
import { getCountryOptions, findOption } from "@/helpers/utils";
import Alert from "@/Components/Alert";

export default function FilingInformation({ auth, filing, companyInfo, countries, formType }) {
    const [selectedFilingType, setSelectedFilingType] = useState(null);
    const [isDisabled, setIsDisabled] = useState(false);
    const countryOptions = getCountryOptions(countries);
    const [countryTaxID, setCountryTaxID] = useState(findOption(countryOptions, filing.company_info?.tax_id_country_id));



    const { data, setData, post, processing, errors, reset } = useForm({
        filing_type_id: filing?.filing_type_id || '',
        // legal_name: companyInfo?.legal_name || '',
        // tax_id_type_id: companyInfo?.tax_id_type_id || '',
        // tax_id_number: companyInfo?.tax_id_number || '',
        // tax_id_country_id: companyInfo?.tax_id_country_id || '',
    })

    const handleFilingTypeChange = (e) => {
        setSelectedFilingType(e.target.value);
        if (e.target.value == 1) {
            setIsDisabled(true);
        } else {
            setIsDisabled(false);
        }
        setData('filing_type_id', e.target.value);
    }


    // const taxIDCountryChange = (e) => {
    //     setData('tax_id_country_id', e.value);
    //     setCountryTaxID({ value: e.value, label: e.label });
    // };

    const handleFormSubmit = (e, action = null) => {
        e.preventDefault();

        if (formType === 'create') {
            post(route('filing.store'));
        } else {
            post(route('filing.update', filing.id));
        }

    }

    const handleSaveExit = (e) => {
        e.preventDefault();

        post(route('filing.updateAndExit', filing.id));
    }

    useEffect(() => {
        setCountryTaxID(findOption(countryOptions, companyInfo?.tax_id_country_id));
    }, [filing, companyInfo]);


    console.log(errors);
    return (
        <AuthSidebarLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Edit Reporting Company</h2>}
        >
            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                    <div className="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                        <Steps
                            className="w-full steps"
                            vertical={false}
                            >
                            <Steps.Step color="primary">Filing Info</Steps.Step>
                            <Steps.Step >Company Information</Steps.Step>
                            <Steps.Step>Company Applicants</Steps.Step>
                            <Steps.Step >Beneficial Owners</Steps.Step>
                            <Steps.Step >Review & Submit</Steps.Step>
                        </Steps>
                    </div>
                    <div className="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                        {Object.keys(errors).length > 0 && <Alert message={errors} type="error" timeOut={20000} />}

                        <h2 className="font-semibold text-2xl text-gray-800 leading-tight">Filing Information</h2>
                        <form>
                            <div className="component-preview p-4 font-sans">
                                <div className="grid gap-4 grid-cols-2 mb-6">
                                    <label className="label">Select Tax Identification Type</label>
                                    <Select
                                        name="filing_type_id"
                                        className={`w-full max-w-md select select-secondary ${errors.filing_type_id} ? 'select-error' : ''`}
                                        value={data.filing_type_id || 'default'}
                                        onChange={handleFilingTypeChange}
                                    >
                                        
                                        <Select.Option value={'default'} disabled>
                                            Select the filing type
                                        </Select.Option>
                                        <Select.Option value={1}>Initial Report</Select.Option>
                                        <Select.Option value={2}>Correction</Select.Option>
                                        <Select.Option value={3}>Update</Select.Option>
                                        <Select.Option value={4}>Newly Exempt</Select.Option>

                                    </Select>
                                </div>
                                {/* <div className="grid gap-4 grid-cols-2 mb-6">
                                    <label className="label">Entity Legal Name</label>
                                    <Input
                                        name="legal_name"
                                        className={`w-full max-w-md input input-secondary ${errors.legal_name ? 'input-error' : ''}`}
                                        bordered={true}
                                        placeholder="Entity Legal Name"
                                        disabled={isDisabled}
                                        value={data.legal_name || ''}
                                        onChange={(e) => setData('legal_name', e.target.value)}
                                    />
                                </div>
                                <div className="grid gap-4 grid-cols-2 mb-6">
                                    <label className="label">Select Tax Identification Type</label>
                                    <Select
                                        name="tax_id_type_id"
                                        className={`w-full max-w-md select select-secondary ${errors.tax_id_type_id ? 'select-error' : ''}`}
                                        value={data.tax_id_type_id || 'default'}
                                        disabled={isDisabled}
                                        onChange={(e) => setData('tax_id_type_id', e.target.value)}
                                    >

                                        <Select.Option value={'default'} disabled>
                                            Pick your Tax Identification Type
                                        </Select.Option>
                                        <Select.Option value={1}>EIN</Select.Option>
                                        <Select.Option value={2}>SSN/TIN</Select.Option>
                                        <Select.Option value={3}>Foreign</Select.Option>
                                    </Select>
                                </div>
                                <div className="grid gap-4 grid-cols-2 mb-6">
                                    <label className="label">Tax ID Number</label>
                                    <Input
                                        name="tax_id_number"
                                        className={`w-full max-w-md input input-secondary ${errors.tax_id_number ? 'input-error' : ''}`}
                                        placeholder="Tax ID Number"
                                        disabled={isDisabled}
                                        onChange={(e) => setData('tax_id_number', e.target.value)}
                                        value={data.tax_id_number || ''}
                                    />
                                </div>
                                <div className={`grid gap-4 grid-cols-2 mb-6`}>
                                    <label className="label">Country/Jurisdiction</label>
                                    <SearchableSelect
                                        name="tax_id_country_id"
                                        disabled={data.tax_id_type_id != '3' || isDisabled}
                                        options={countryOptions}
                                        placeholder="Select the country of your tax ID"
                                        handleChange={(value) => taxIDCountryChange(value)}
                                        value={countryTaxID}
                                        error={errors.tax_id_country_id}
                                    />

                                </div> */}
                            </div>
                            <div className="flex justify-between">
                                <Button
                                    tag="a"
                                    href={route('filing.index')}
                                    color="secondary"
                                    className="font-bold text-white btn btn-secondary"
                                >
                                    Cancel
                                </Button>
                                <Button
                                    color="accent"
                                    className={`btn font-bold text-white 
                                        ${Object.keys(errors).length > 0 ? 'btn-error' : 'btn-primary'}
                                        `}
                                    onClick={handleSaveExit}
                                > Save & Exit
                                </Button>
                                <Button
                                    color="primary"
                                    className={`btn font-bold text-white 
                                        ${Object.keys(errors).length > 0 ? 'btn-error' : 'btn-primary'}
                                        `}
                                    // these values will change and redirect will happen at controller
                                    onClick={handleFormSubmit}
                                >
                                    {processing ? (
                                        <span className="loading loading-spinner">Processing</span>
                                    ) : Object.keys(errors).length > 0 ? 'Fix Errors' : 'Save & Continue'}
                                </Button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </AuthSidebarLayout>
    )
 }