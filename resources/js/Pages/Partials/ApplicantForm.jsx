import { useEffect, useState } from "react";
import { useForm, usePage } from '@inertiajs/react';
import { Checkbox, Input, Select, Button, FileInput } from "react-daisyui";
import InputError from '@/Components/InputError';
import SearchableSelect from "@/Components/SearchableSelect";
import Alert from "@/Components/Alert";
import { useAppOwnerForm } from "@/hooks/useAppOwnerForm";
import { QuestionMarkCircleIcon } from "@heroicons/react/24/outline";
import PhoneNumberInput from "@/Components/PhoneNumberInput";


// TODO: ensure zip code is max of 9 characters
export default function ApplicantForm({ applicant, title, filing, companyInfo, countries, states, tribes, routeInfo, resetForm }) {


    const [applicantFormData, setApplicantFormData] = useState({ ...applicant });
    const [isExistingReportingCompany, setIsExistingReportingCompany] = useState(companyInfo?.existing_reporting_company || false);
    const [isForeignInvestment, setIsForeignInvestment] = useState(companyInfo?.foreign_investment || false);
    const [wasSuccessful, setWasSuccessful] = useState(false);

    const { data, setData, post, put, errors, processing, reset } = useForm({
        fincen_id: applicant?.fincen_id || '',
        last_name: applicantFormData?.last_name || '',
        first_name: applicantFormData?.first_name || '',
        middle_name: applicantFormData?.middle_name || '',
        suffix: applicantFormData?.suffix || '',
        dob: applicantFormData?.dob || '',
        address_type: applicantFormData?.address_type || '',
        address: applicantFormData?.address || '',
        city: applicantFormData?.city || '',
        state_id: applicantFormData?.state_id || '',
        country_id: applicantFormData?.country_id || '',
        zip: applicantFormData?.zip || '',
        id_type: applicantFormData?.id_type || '',
        id_number: applicantFormData?.id_number || '',
        id_document_country: applicantFormData?.id_document_country || '',
        id_document_state: applicantFormData?.id_document_state || '',
        id_document_tribe: applicantFormData?.id_document_tribe || '',
        tribal_other_name: applicantFormData?.tribal_other_name || '',
        email: applicantFormData?.email || '',
        phone: applicantFormData?.phone || '',
        info_verified: applicantFormData?.info_verified_at ? true : false,
        file: ''
    });


    const {
        countryOptions,
        stateOptions,
        tribeOptions,
        currentCountry,
        currentState,
        idDocumentCountry,
        idDocumentState,
        idDocumentTribe,
        IDType,
        showTribeOtherName,
        isFinCendValid,
        validation,
        handleCurrentCountryChange,
        handleCurrentStateChange,
        handleIDTypeChange,
        handleIDCountryChange,
        handleIDStateChange,
        handleIDTribeChange,
        isFincenIdValid
    } = useAppOwnerForm(applicantFormData, countries, states, tribes);


    // const checkFincenId = (fincenId) => {
    //     const update = isFincenIdValid(fincenId);
    //     setData({ ...data, ...update });
    // };

    const checkFincenId = (fincenId) => {
        setData('fincen_id', fincenId);
        isFincenIdValid(fincenId); 
    };

    const onChangeCurrentCountry = (selectedOption) => {
        const updates = handleCurrentCountryChange(selectedOption);
        setData({ ...data, ...updates });
    }

    const onChangeCurrentState = (selectedOption) => {
        handleCurrentStateChange(selectedOption);
        setData('state_id', selectedOption.value);
    }

    const onIDTypeChange = (selectedOption) => {
        const updates = handleIDTypeChange(selectedOption);
        setData({ ...data, ...updates });
    }

    const onIDCountryChange = (selectedOption) => {
        const updates = handleIDCountryChange(selectedOption);
        setData({ ...data, ...updates });
    }

    const onIDStateChange = (selectedOption) => {
        const updates = handleIDStateChange(selectedOption);
        setData({ ...data, ...updates });
    }

    const onIDTribeChange = (selectedOption) => {
        const updates = handleIDTribeChange(selectedOption);
        setData({ ...data, ...updates });
    }


    const handleFormSubmit = (e) => {
        e.preventDefault();

        const { name, type, params } = routeInfo;

        const requestOptions = {
            onSuccess: () => {
                resetForm();
                setWasSuccessful(true);
            },
        }
        if (type === 'put') {
            put(route(name, params), requestOptions);
        } else if (type === 'post') {
            post(route(name, params), requestOptions);
        } else {
            console.warn(`Unsupported request type: ${type}`);
        }

    };

    const handleSaveExit = (e) => {
        e.preventDefault();

        if (!companyInfo?.id) {
            // post(route('filing.applicants.exit', { id: filing.id }));
        } else {
            post(route('filing.applicants.update.exit', { id: filing.id, applicant_id: applicantFormData.id }));

        }
    };


    useEffect(() => {
        setApplicantFormData({ ...applicant })

    }, [applicant, isFinCendValid]);

    useEffect(() => {
        if (data.fincen_id) {
            checkFincenId(data.fincen_id)
        }
    }, [applicantFormData]);

    return (
        // TODO: would be cool to have a link to some documentation here on how to fill out this form

        <div className="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
            {Object.keys(errors).length > 0 && <Alert message={errors} type="error" timeOut={10000} />}

            <h2 className="font-semibold text-2xl text-gray-800 leading-tight mb-3">{title || `Company Applicant`}</h2>
            <form encType="multipart/form-data">
                <div className="component-preview p-4 font-sans">

                    <div className="w-full border-b-2 mb-2">
                        <h3 className="font-semibold text-lg text-gray-800 leading-tight">Company Applicant FinCEN ID:</h3>
                    </div>
                    <div className="grid gap-4 grid-cols-2 mb-6">
                        <label className="label">FinCEN ID</label>
                        <Input
                            name="fincen_id"
                            className={`max-w-md input input-bordered ${validation.fincen_id ? 'input-error' : isFinCendValid ? 'input-success' : 'input-secondary'}`}
                            bordered={true}
                            placeholder="Company Applicant FinCEN ID"
                            disabled={data.existing_reporting_company}
                            onChange={(e) => checkFincenId(e.target.value)}
                            value={data.fincen_id}

                        />
                        <InputError message={errors.fincen_id || validation.fincen_id} className="" />
                    </div>

                    <div className="w-full border-b-2 mb-2">
                        <h3 className="font-semibold text-lg text-gray-800 leading-tight">Full Legal Name and Date Of Birth:</h3>
                    </div>

                    <div className="grid gap-4 grid-cols-2 mb-6">
                        <label className="label">Individual's Last name</label>
                        <Input
                            name="last_name"
                            className={`max-w-md input input-bordered input-secondary ${errors.last_name ? 'input-error' : ''}`}
                            bordered={true}
                            placeholder="Applicant's Last name"
                            value={data.last_name}
                            onChange={(e) => setData('last_name', e.target.value)}
                        // disabled={ }
                        />
                    </div>
                    <div className="grid gap-4 grid-cols-2 mb-6">
                        <label className="label">Individual's First name</label>
                        <Input
                            name="first_name"
                            className={`max-w-md input input-bordered input-secondary ${errors.first_name ? 'input-error' : ''}`}
                            bordered={true}
                            placeholder="Applicant's First name"
                            value={data.first_name}
                            onChange={(e) => setData('first_name', e.target.value)}
                        // disabled={ }
                        />
                    </div>
                    <div className="grid gap-4 grid-cols-2 mb-6">
                        <label className="label">Individual's Middle name</label>
                        <Input
                            name="middle_name"
                            className={`max-w-md input input-bordered input-secondary ${errors.middle_name ? 'input-error' : ''}`}
                            bordered={true}
                            placeholder="Applicant's Middle name"
                            value={data.middle_name}
                            onChange={(e) => setData('middle_name', e.target.value)}
                        // disabled={ }
                        />
                    </div>
                    <div className="grid gap-4 grid-cols-2 mb-6">
                        <label className="label">Individual's Suffix</label>
                        <Input
                            name="suffix"
                            className={`max-w-md input input-bordered input-secondary ${errors.suffix ? 'input-error' : ''}`}
                            bordered={true}
                            placeholder="Applicant's Suffix"
                            value={data.suffix}
                            onChange={(e) => setData('suffix', e.target.value)}
                        // disabled={ }

                        />
                    </div>
                    <div className="grid gap-4 grid-cols-2 mb-6">
                        <label className="label">Individual's Date of Birth</label>
                        <Input
                            name="dob"
                            className={`max-w-md input input-bordered input-secondary ${errors.dob ? 'input-error' : ''}`}
                            bordered={true}
                            placeholder="Applicant's Date of Birth"
                            type="date"
                            value={data.dob}
                            onChange={(e) => setData('dob', e.target.value)}
                        // disabled={ }
                        />
                    </div>
                    <div className="grid gap-4 grid-cols-2 mb-6">
                        <label className="label">Individual's Email
                            <div className="tooltip" data-tip="Applicant's Email is not part of the official BIOR application.">
                                <QuestionMarkCircleIcon className="h-5 w-5" />
                            </div>
                        </label>
                        <Input
                            name="email"
                            className={`max-w-md input input-bordered input-secondary ${errors.email ? 'input-error' : ''}`}
                            bordered={true}
                            placeholder="Applicant's Email"
                            value={data.email}
                            onChange={(e) => setData('email', e.target.value)}
                        />
                    </div>
                    <div className="grid gap-4 grid-cols-2 mb-6">
                        <label className="label">Individual's Phone Number
                            <div className="tooltip" data-tip="Applicant's Phone Number is not part of the official BIOR application.">
                                <QuestionMarkCircleIcon className="h-5 w-5" />
                            </div>
                        </label>
                        <PhoneNumberInput
                            label="Applicant's Phone"
                            name="phone"
                            value={data.phone}
                            setPhoneNumber={(e) => setData('phone', e)}
                        />
                    </div>
                    <div className="w-full border-b-2 mb-2">
                        <h3 className="font-semibold text-lg text-gray-800 leading-tight">Current Address:</h3>
                    </div>
                    <div className="grid gap-4 grid-cols-2 mb-6">
                        <label className="label">Address Type</label>
                        <Select
                            name="address_type"
                            className={`w-full max-w-md select select-secondary ${errors.address_type ? 'select-error' : ''}`}
                            // defaultValue={'default'}
                            value={data.address_type || 'default'}
                            onChange={(e) => setData('address_type', e.target.value)}
                        // disabled={ }

                        >
                            <Select.Option value={'default'} disabled>
                                Select the address type
                            </Select.Option>
                            <Select.Option value={'business'}>Business</Select.Option>
                            <Select.Option value={'residential'}>Residential</Select.Option>

                        </Select>
                    </div>
                    <div className="grid gap-4 grid-cols-2 mb-6">
                        <label className="label">Address (number, street, and apt. or suite no.)</label>
                        <Input
                            name="address"
                            className={`max-w-md input input-bordered input-secondary ${errors.address ? 'input-error' : ''}`}
                            bordered={true}
                            placeholder="Applicant's Current Address"
                            value={data.address}
                            onChange={(e) => setData('address', e.target.value)}
                        // disabled={ }

                        />
                    </div>
                    <div className="grid gap-4 grid-cols-2 mb-6">
                        <label className="label">City</label>
                        <Input
                            name="city"
                            className={`max-w-md input input-bordered input-secondary ${errors.city ? 'input-error' : ''}`}
                            bordered={true}
                            placeholder="Applicant's Current City"
                            value={data.city}
                            onChange={(e) => setData('city', e.target.value)}
                        // disabled={ }

                        />
                    </div>
                    <div className="grid gap-4 grid-cols-2 mb-6">
                        <label className="label">Country/Jurisdiction </label>
                        <SearchableSelect
                            name="country_id"
                            placeholder={'Select Applicants Country'}
                            options={countryOptions}
                            handleChange={(e) => onChangeCurrentCountry(e)}
                            value={currentCountry}
                            error={errors.country_id}
                        />

                    </div>
                    <div className="grid gap-4 grid-cols-2 mb-6">
                        <label className="label">State</label>
                        <SearchableSelect
                            name="state_id"
                            placeholder={'Select Applicants State'}
                            options={stateOptions}
                            handleChange={(e) => onChangeCurrentState(e)}
                            value={currentState}
                            error={errors.state_id}
                            disabled={(currentCountry.label !== 'United States')}

                        />
                    </div>
                    <div className="grid gap-4 grid-cols-2 mb-6">
                        <label className="label">Zip Code/Foreign Postal code</label>
                        <Input
                            name="zip"
                            className={`max-w-md input input-bordered input-secondary ${errors.zip ? 'input-error' : ''}`}
                            bordered={true}
                            placeholder="Applicant Zip or Foreign Postal code"
                            value={data.zip}
                            onChange={(e) => setData('zip', e.target.value)}
                        // disabled={ }

                        />
                    </div>
                    <div className="w-full border-b-2 mb-2">
                        <h3 className="font-semibold text-lg text-gray-800 leading-tight">Form of Identification and Issuing Jurisdiction:</h3>
                    </div>
                    <div className="grid gap-4 grid-cols-2 mb-6">
                        <label className="label">Identifying Document Type</label>
                        <Select
                            name="id_type"
                            className={`w-full max-w-md select select-secondary ${isFinCendValid ? 'disabledBackground' : ''
                                } ${errors.id_type ? 'select-error' : ''
                                }`}
                            defaultValue={data.id_type !== '' ? data.id_type : 'default'}
                            disabled={isFinCendValid}
                            onChange={(e) => { onIDTypeChange(e.target.value) }}

                        >
                            <Select.Option value={'default'} disabled>
                                Select Document Type
                            </Select.Option>
                            <Select.Option value={'us_passport'}>U.S. Passport</Select.Option>
                            <Select.Option value={'drivers_license'}>State Issued Driver's License</Select.Option>
                            <Select.Option value={'state_tribe_id'}>State/Local/Tribal ID</Select.Option>
                            <Select.Option value={'foreign_passport'}>Foreign Passport</Select.Option>
                        </Select>
                    </div>
                    <div className="grid gap-4 grid-cols-2 mb-6">
                        <label className="label">Identifying Document Number</label>
                        <Input
                            name="id_number"
                            className={`max-w-md input input-bordered input-secondary ${errors.id_number ? 'input-error' : ''}`}
                            bordered={true}
                            placeholder="Document Number"
                            // disabled={generateDisabledState()}
                            value={data.id_number}
                            onChange={(e) => setData('id_number', e.target.value)}
                            disabled={isFinCendValid}
                        />
                    </div>
                    <div className="grid gap-4 grid-cols-2 mb-6">
                        <label className="label">Identifying Document Issuing Country/Jurisdiction</label>
                        <SearchableSelect
                            name="id_document_country"
                            placeholder={'Select the Country of Identification Issuance'}
                            options={countryOptions}
                            handleChange={(e) => onIDCountryChange(e)}
                            value={idDocumentCountry}
                            error={errors.id_document_country}
                            disabled={isFinCendValid}

                        />
                    </div>
                    <div className="grid gap-4 grid-cols-2 mb-6">
                        <label className="label">State</label>
                        <SearchableSelect
                            name="id_document_state"
                            placeholder={'Select the State'}
                            options={stateOptions}
                            handleChange={(e) => onIDStateChange(e)}
                            value={idDocumentState}
                            error={errors.id_document_state}
                            disabled={isFinCendValid || IDType === 'us_passport' || IDType === 'foreign_passport'}
                        />
                    </div>
                    <div className="grid gap-4 grid-cols-2 mb-6">
                        <label className="label">Tribal Jurisdiction of formation</label>
                        <SearchableSelect
                            name="id_document_tribe"
                            placeholder={'Select the Tribal Jurisdiction of Formation'}
                            options={tribeOptions}
                            handleChange={(e) => onIDTribeChange(e)}
                            value={idDocumentTribe}
                            error={errors.id_document_tribe}
                            disabled={isFinCendValid || IDType === 'us_passport' || IDType === 'foreign_passport'}
                        />
                    </div>
                    <div className={`grid gap-4 grid-cols-2 mb-6 ${!showTribeOtherName ? 'hidden' : ''}`}>
                        <label className="label">Name of the other Tribe</label>
                        <Input
                            name="tribal_other_name"
                            className={`max-w-md input input-bordered input-secondary ${errors.tribal_other_name ? 'input-error' : ''}`}
                            placeholder="Name of the other Tribe"
                            disabled={isFinCendValid}
                            value={data.tribal_other_name}
                            onChange={(e) => setData('tribal_other_name', e.target.value)}
                        />
                    </div>
                    <div className="grid gap-4 grid-cols-2 mb-6">
                        <label htmlFor="" className="lable">Identifying Document File</label>
                        <FileInput
                            name="file"
                            className={`file-input file-input-bordered w-full max-w-md ${isFinCendValid ? 'disabledBackground' : ''} ${errors.file ? 'file-input-error' : ''}`}
                            bordered={true}
                            size="lg"
                            color="neutral"
                            disabled={isFinCendValid}
                            onChange={(e) => setData('file', e.target.files[0])}

                        />
                    </div>
                    <div className="flex justify-end mb-6">
                        {applicant?.file_name && (
                            <span className="text-sm text-amber-600">a file exists for this applicant. If you upload a new file, it will replace the existing file.</span>
                        )}
                    </div>
                    <div className="flex justify-between mt-4">
                        <Button color="secondary" className="font-bold text-white btn btn-secondary" >Cancel</Button>


                        <Button
                            color="primary"
                            className={`btn font-bold text-white ${wasSuccessful ? 'btn-success' : (Object.keys(errors).length > 0 ? 'btn-error' : 'btn-primary')
                                }`}
                            onClick={handleFormSubmit}
                        >
                            {
                                processing
                                    ? <span className="loading loading-spinner">Processing</span>
                                    : wasSuccessful
                                        ? 'Owner Saved'
                                        : (Object.keys(errors).length > 0 ? 'Fix Errors' : 'Save Applicant')
                            }
                        </Button>
                    </div>
                </div>
            </form>
        </div>
    )
}