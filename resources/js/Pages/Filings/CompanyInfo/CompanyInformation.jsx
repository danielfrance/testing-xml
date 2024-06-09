import { useState, useEffect } from "react";
import { useForm, Link } from '@inertiajs/react';
import AuthSidebarLayout from "@/Layouts/AuthSidebarLayout";
import Alert from "@/Components/Alert";
import { Checkbox, Input, Select, Button, Steps } from "react-daisyui"
import { PlusIcon, XMarkIcon } from "@heroicons/react/24/outline";
import SearchableSelect from "@/Components/SearchableSelect";
import CompanyInfoFormationInfo from "./FormationInfo";

// TODO: ensure zip code is max of 9 characters

export default function CompanyInformation({ auth, companyInfo, filing, taxIdTypes, status, countries, states, tribes }) {


    const [isForeignPooledInvestment, setIsForeignPooledInvestment] = useState({});
    const [isExistingReportingCompany, setIsExistingReportingCompany] = useState({});

    const [alternateNames, setAlternateNames] = useState(() => {
        try {
            const parsedNames = JSON.parse(companyInfo?.alternate_name || "[]");
            return Array.isArray(parsedNames) ? parsedNames : [];
        } catch (error) {
            return [];
        }
    });


    const { data, setData, put, post, processing, errors, reset } = useForm({
        get_fincen: companyInfo?.get_fincen || false,
        foreign_pooled_investment: companyInfo?.foreign_pooled_investment || false,
        legal_name: companyInfo?.legal_name || '',
        alternate_name: JSON.stringify(alternateNames), // Set initial value from alternateNames
        tax_id_type_id: companyInfo?.tax_id_type_id || '',
        tax_id_number: companyInfo?.tax_id_number || '',
        tax_id_country_id: companyInfo?.tax_id_country_id || '',
        country_formation_id: companyInfo?.country_formation_id || '',
        formation_type: companyInfo?.formation_type || '',
        state_formation_id: companyInfo?.state_formation_id || '',
        tribal_formation_id: companyInfo?.tribal_formation_id || '',
        tribal_other_name: companyInfo?.tribal_other_name || '',
        current_street_address: companyInfo?.current_street_address || '',
        current_city: companyInfo?.current_city || '',
        current_state_id: companyInfo?.current_state_id || '',
        current_country_id: companyInfo?.current_country_id || '',
        zip: companyInfo?.zip || '',
        existing_reporting_company: companyInfo?.existing_reporting_company || false,
    });

    const countryOptions = countries
        .map(country => {
            return {
                value: country.id,
                label: country.name,
                territory: country.us_territory
            }
        })
        .sort((a, b) => {
            if (a.label === 'United States') return -1;
            if (b.label === 'United States') return 1;
            return a.label.localeCompare(b.label);
        });

    const usTerritories = countries
        .filter(country => {
            return country.us_territory === true || country.name === 'United States';
        })
        .map(country => {
            return {
                value: country.id,
                label: country.name,
                territory: country.us_territory
            }
        })
        .sort((a, b) => {
            if (a.label === 'United States') return -1; // 'United States' at the top
            if (b.label === 'United States') return 1; // 'United States' at the top
            return a.label.localeCompare(b.label); // Sort alphabetically
        });


    const [currentCountry, setCurrentCountry] = useState(countryOptions.find(country => {
        return country.value === companyInfo?.current_country_id;
    }) || {});

    const stateOptions = states
        .map(state => {
            return {
                value: state.id,
                label: state.name
            }
        })
        .sort((a, b) => {
            return a.label.localeCompare(b.label);
        });

    const [currentState, setCurrentState] = useState(stateOptions.find(state => {
        return state.value === companyInfo?.current_state_id;
    }) || {});


    const handleFormSubmit = (e) => {
        e.preventDefault();

        if (!companyInfo?.id) {
            post(route('filing.company_info.store', { id: filing.id }));
        } else {
            post(route('filing.company_info.update', { id: filing.id }));

        }

    }

    const handleSaveExit = (e) => {
        e.preventDefault();

        if (!companyInfo?.id) {
            post(route('filing.company_info.store.exit', { id: filing.id }));
        } else {
            post(route('filing.company_info.update.exit', { id: filing.id }));

        }
    }

    const addAlternateName = () => {
        setAlternateNames([...alternateNames, ""]);
    };

    const removeAlternateName = (index) => {
        const updatedNames = [...alternateNames];
        updatedNames.splice(index, 1);
        setAlternateNames(updatedNames);
    };

    const handleNameChange = (index, value) => {
        const updatedNames = [...alternateNames];
        updatedNames[index] = value;
        setAlternateNames(updatedNames);
    };

    const handleForeignPooledInvestment = (e) => {
        // handle logic here
        setIsForeignPooledInvestment({
            ...isForeignPooledInvestment,
            value: e.target.checked,
            message: e.target.checked ? 'Company Applicant section will be skipped and only 1 beneficial owner will be required' : ''
        });
        setData('foreign_pooled_investment', e.target.checked);

    }

    const handleExistingReportingCompany = (e) => {
        setIsExistingReportingCompany({
            ...isExistingReportingCompany,
            value: e.target.checked,
            message: e.target.checked ? 'Company Applicant section will be skipped as company was created prior to January 1, 2024' : ''
        });
        setData('existing_reporting_company', e.target.checked);
    }

    const handleCurrentCountryChange = (e) => {
        let newData = {
            current_country_id: e.value,
        }

        setCurrentCountry(e);

        if (e.territory) {

            const filteredTerritory = stateOptions.filter(state => state.label === e.label);

            newData.formation_type = 'domestic';
            newData.current_state_id = filteredTerritory[0].value;


            setCurrentState({
                value: filteredTerritory[0].value,
                label: filteredTerritory[0].label,
            });
        }

        setData({ ...data, ...newData });

    }

    const handleCurrentStateChange = (e) => {
        setCurrentState(e);
        setData('current_state_id', e.value);
    }


    console.log(errors);
    console.log(data);

    useEffect(() => {
        // Update data.alternate_name only if it's different from the current value
        if (JSON.stringify(alternateNames) !== data.alternate_name) {
            setData("alternate_name", JSON.stringify(alternateNames));
        }
    }, [alternateNames, data.alternate_name, data, currentCountry, currentState]);

    // TODO: create an alert for confirming the change in tax id type and tax id country

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
                    {isExistingReportingCompany.value && (
                        <Alert className="mb-4" message={isExistingReportingCompany.message} type="warning" />
                    )}

                    {Object.keys(errors).length > 0 && <Alert message={errors} type="error" timeOut={120000} />}


                    <div className="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                        <h2 className="font-semibold text-2xl text-gray-800 leading-tight">Company Information {companyInfo?.legal_name ? `: ${companyInfo.legal_name}` : ''}</h2>


                        <form>
                            <div className="component-preview p-4 font-sans">
                                <div className="grid gap-4 grid-cols-2 mt-4 mb-6 items-center" >
                                    <label className="label">Tax Identification Type</label>
                                    <Select
                                        className={`w-full max-w-md select select-secondary selectDisabled`}
                                        value={filing.filing_type_id || 'default'}
                                        disabled
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

                                <div className="grid gap-4 grid-cols-2 mb-6 items-center" >
                                    <label className="label w-full">Select to request to receive FinCEN Identifier (FinCEN ID)</label>
                                    <Checkbox
                                        name="get_fincen"
                                        size="lg"
                                        className="checkbox checkbox-secondary"
                                        value={data.get_fincen}
                                        checked={data.get_fincen}
                                        onChange={(e) => setData('get_fincen', e.target.checked)}
                                    />

                                </div>
                                <div className="grid gap-4 grid-cols-2 mb-6 items-center" >
                                    <label className="label w-full">Foreign Pooled Investment Vehicle?</label>
                                    <Checkbox
                                        name="foreign_pooled_investment"
                                        size="lg"
                                        className="checkbox checkbox-secondary"
                                        onChange={handleForeignPooledInvestment}
                                        value={data.foreign_pooled_investment}
                                        checked={data.foreign_pooled_investment}

                                    />

                                </div>
                                <div className="grid gap-4 grid-cols-2 mb-6 items-center" >
                                    <label className="label w-full"><span>Existing reporting company (Check if Reporting Company was created or registered <strong> <em>before</em> </strong> January 1, 2024)</span></label>
                                    <Checkbox
                                        name="existing_reporting_company"
                                        size="lg"
                                        className="checkbox checkbox-secondary"
                                        value={data.existing_reporting_company}
                                        checked={data.existing_reporting_company}
                                        onChange={(e) => handleExistingReportingCompany(e)}
                                    />

                                </div>
                                <div className="w-full border-b-2 mb-2">
                                    <h3 className="font-semibold text-lg text-gray-800 leading-tight">Full Legal name and alternate names</h3>
                                </div>
                                <div className="grid gap-4 grid-cols-2 mb-6">
                                    <label className="label">Reporting Company Legal Name</label>
                                    <Input
                                        name="legal_name"
                                        className={`max-w-md input input-bordered input-secondary ${errors.legal_name ? 'input-error' : ''}`}
                                        bordered={true}
                                        placeholder="Entity Legal Name"
                                        value={data.legal_name}
                                        onChange={(e) => setData('legal_name', e.target.value)}

                                    />
                                </div>

                                <div className="grid gap-4 grid-cols-2 mb-6" >
                                    <label className="label">Alternate Name </label>
                                    <div>
                                        {alternateNames.map((name, index) => (

                                            <div key={index} className="flex items-center mb-3">
                                                <Input
                                                    type="text"
                                                    name={`alternate_name[${index}]`}
                                                    className={`w-3/4 input input-bordered input-secondary ${errors.alternate_name ? 'input-error' : ''}`}
                                                    bordered={true}
                                                    placeholder="Entity Alternate Name"
                                                    value={name}
                                                    onChange={(e) => handleNameChange(index, e.target.value)}
                                                />
                                                <button type="button" className="btn btn-square btn-md ml-1 " onClick={() => removeAlternateName(index)}>
                                                    <XMarkIcon className="h-5 w-5" />
                                                </button>
                                            </div>
                                        ))}
                                        <button type="button" className="btn btn-md btn-accent " onClick={addAlternateName}>
                                            <PlusIcon className="h-5 w-5" />
                                            Add Alternate Name
                                        </button>
                                    </div>
                                </div >


                                <CompanyInfoFormationInfo
                                    filing={filing}
                                    countryOptions={countryOptions}
                                    states={states.map(state => {
                                        return {
                                            value: state.id,
                                            label: state.name
                                        }
                                    })}
                                    tribes={tribes.map(tribe => {
                                        return {
                                            value: tribe.id,
                                            label: tribe.name
                                        }
                                    })}
                                    taxIdTypes={taxIdTypes}
                                    data={data}
                                    setData={setData}
                                    errors={errors}

                                />

                                <div className="w-full border-b-2 mb-2">
                                    <h3 className="font-semibold text-lg text-gray-800 leading-tight">Current U.S. Address</h3>
                                </div>
                                <div className="grid gap-4 grid-cols-2 mb-6">
                                    <label className="label">Street Address</label>
                                    <Input
                                        name="current_street_address"
                                        className={`max-w-md input input-bordered input-secondary ${errors.current_street_address ? 'input-error' : ''}`} placeholder="Street Address"
                                        value={data.current_street_address}
                                        onChange={(e) => setData('current_street_address', e.target.value)}
                                    />
                                </div>
                                <div className="grid gap-4 grid-cols-2 mb-6">
                                    <label className="label">City</label>
                                    <Input
                                        name="current_city"
                                        className={`max-w-md input input-bordered input-secondary ${errors.current_city ? 'input-error' : ''}`}
                                        placeholder="City"
                                        value={data.current_city}
                                        onChange={(e) => setData('current_city', e.target.value)}
                                    />
                                </div>
                                <div className="grid gap-4 grid-cols-2 mb-6">
                                    <label className="label">U.S. or U.S. Territory</label>
                                    <SearchableSelect
                                        name="current_country_id"
                                        placeholder="Select Current U.S. Territory"
                                        handleChange={(e) => handleCurrentCountryChange(e)}
                                        options={usTerritories}
                                        value={currentCountry}
                                        error={errors.current_country_id}

                                    />
                                </div>

                                <div className="grid gap-4 grid-cols-2 mb-6">
                                    <label className="label">State</label>
                                    <SearchableSelect
                                        name="current_state_id"
                                        placeholder="Select Current U.S. Territory"
                                        handleChange={(e) => handleCurrentStateChange(e)}
                                        options={stateOptions}
                                        value={currentState}
                                        disabled={currentCountry.label !== 'United States'}
                                        error={errors.current_state_id}
                                    />

                                </div>
                                <div className="grid gap-4 grid-cols-2 mb-6">
                                    <label className="label">ZIP</label>
                                    <Input
                                        name="zip"
                                        className={`max-w-md input input-bordered input-secondary ${errors.zip ? 'input-error' : ''}`}
                                        placeholder="ZIP"
                                        value={data.zip}
                                        onChange={(e) => setData('zip', e.target.value)}
                                    />
                                </div>

                                <div className="flex justify-between">
                                    <Button
                                        tag="a"
                                        href={route('filing.show', filing.id)}
                                        color="secondary"
                                        className="font-bold text-white btn btn-secondary"
                                    >
                                        Back
                                    </Button>
                                    <Link
                                        onClick={(e) => handleSaveExit(e)}
                                        className="btn btn-accent font-bold text-white">Save & Exit</Link>
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

                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </AuthSidebarLayout>
    )
}