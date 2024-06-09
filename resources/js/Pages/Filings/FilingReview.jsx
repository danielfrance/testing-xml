import { useState } from "react";
import AuthSidebarLayout from "@/Layouts/AuthSidebarLayout";
import { Button, Steps, Collapse } from "react-daisyui"
import ReviewApplicants from "../Partials/ReviewApplicants";

export default function FilingReview({ auth, filing, companyInfo, companyApplicants, beneficialOwners }) {
    const [openCollapses, setOpenCollapses] = useState([]);


    const handleCollapseClick = (collapseId) => {
        setOpenCollapses(prevCollapses => {
            if (prevCollapses.includes(collapseId)) {
                return prevCollapses.filter(id => id !== collapseId);
            } else {
                return [...prevCollapses, collapseId];
            }
        });
    }

    const isCollapseOpen = (collapseId) => {
        return openCollapses.includes(collapseId);
    }

    // const getBackgroundColor = (collapseId) => {
    //     return isCollapseOpen(collapseId) ? 'bg-base-100' : 'bg-base-200';
    // }

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
                            <Steps.Step color="primary">Company Applicants</Steps.Step>
                            <Steps.Step color="primary">Beneficial Owners</Steps.Step>
                            <Steps.Step color="primary">Review & Submit</Steps.Step>
                        </Steps>
                    </div>
                    <div className="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                        <h2 className="font-semibold text-2xl text-gray-800 leading-tight mb-3">Review Filing</h2>

                        <form action="">
                            {/* Filing Information */}
                            <Collapse.Details
                                icon="arrow"
                                className={`p-3 mb-3 border border-base-300 `}

                            >
                                <Collapse.Details.Title
                                    onClick={() => handleCollapseClick('filingInfo')}
                                    className="text-xl font-medium mb-3">
                                    Filing Information
                                </Collapse.Details.Title>
                                <Collapse.Content>
                                    <div className="grid gap-4 grid-cols-2 mb-6 border border-t-0 border-l-0 border-r-0 border-b-1">
                                        <label className="label">Select Tax Identification Type</label>
                                        <p className="max-w-md font-semibold">{filing.filing_type_name}</p>
                                    </div>

                                    <div className="grid gap-4 grid-cols-2 mb-6 border border-t-0 border-l-0 border-r-0 border-b-1">
                                        <label className="label">Entity Legal Name</label>
                                        <p className="max-w-md font-semibold">{filing.company_info?.legal_name}</p>
                                    </div>

                                    <div className="grid gap-4 grid-cols-2 mb-6 border border-t-0 border-l-0 border-r-0 border-b-1">
                                        <label className="label">Select Tax Identification Type</label>
                                        <p className="max-w-md font-semibold">{filing.company_info?.tax_id_type_name}</p>
                                    </div>

                                    <div className="grid gap-4 grid-cols-2 mb-6 border border-t-0 border-l-0 border-r-0 border-b-1">
                                        <label className="label">Tax ID Number</label>
                                        <p className="max-w-md font-semibold">{filing.company_info.tax_id_number}</p>
                                    </div>
                                </Collapse.Content>
                            </Collapse.Details>

                            {/* Company Information */}
                            <Collapse.Details
                                icon="arrow"
                                className={`p-3 mb-3 border border-base-300 `}

                            >
                                <Collapse.Details.Title
                                    onClick={() => handleCollapseClick('companyInfo')}
                                    className="text-xl font-medium mb-3">
                                    Company Information
                                </Collapse.Details.Title>
                                <Collapse.Content>
                                    <div className="grid gap-4 grid-cols-2 mb-6 border border-t-0 border-l-0 border-r-0 border-b-1 items-center">
                                        <label className="label w-full">Select to request to receive FinCEN Identifier (FinCEN ID)</label>
                                        <p className="max-w-md font-semibold">{filing.company_info?.get_fincen ? 'Yes' : 'No'}</p>
                                    </div>

                                    <div className="grid gap-4 grid-cols-2 mb-6 border border-t-0 border-l-0 border-r-0 border-b-1 items-center">
                                        <label className="label w-full">Foreign Pooled Investment Vehicle?</label>
                                        <p className="max-w-md font-semibold">{filing.company_info?.foreign_pooled_investment ? 'Yes' : 'No'}</p>
                                    </div>

                                    <div className="grid gap-4 grid-cols-2 mb-6 border border-t-0 border-l-0 border-r-0 border-b-1">
                                        <label className="label">Reporting Company Legal Name</label>
                                        <p className="max-w-md font-semibold">{filing.company_info?.legal_name}</p>
                                    </div>

                                    <div className="grid gap-4 grid-cols-2 mb-6 border border-t-0 border-l-0 border-r-0 border-b-1">
                                        <label className="label">Alternate Name (e.g. trade name, DBA)</label>
                                        <p className="max-w-md font-semibold">{filing.company_info?.alternate_name}</p>
                                    </div>

                                    <div className="grid gap-4 grid-cols-2 mb-6 border border-t-0 border-l-0 border-r-0 border-b-1">
                                        <label className="label">Tax Identification Type</label>
                                        <p className="max-w-md font-semibold">{filing.company_info?.tax_id_type_name}</p>
                                    </div>

                                    <div className="grid gap-4 grid-cols-2 mb-6 border border-t-0 border-l-0 border-r-0 border-b-1">
                                        <label className="label">Tax ID Number</label>
                                        <p className="max-w-md font-semibold">{filing.company_info?.tax_id_number}</p>
                                    </div>

                                    <div className="grid gap-4 grid-cols-2 mb-6 border border-t-0 border-l-0 border-r-0 border-b-1">
                                        <label className="label">Tax ID Country/Jurisdiction</label>
                                        <p className="max-w-md font-semibold">{filing.company_info?.tax_id_type_name === 'Foreign' ? filing.company_info?.tax_id_country_name : 'N/A'}</p>
                                    </div>

                                    <div className="grid gap-4 grid-cols-2 mb-6 border border-t-0 border-l-0 border-r-0 border-b-1">
                                        <label className="label">Country/Jurisdiction of Company Formation</label>
                                        <p className="max-w-md font-semibold">{filing.company_info?.country_formation_name}</p>
                                    </div>

                                    <div className="grid gap-4 grid-cols-2 mb-6 border border-t-0 border-l-0 border-r-0 border-b-1">
                                        <label className="label">State of Company Formation</label>
                                        <p className="max-w-md font-semibold">{filing.company_info?.state_formation_name}</p>
                                    </div>

                                    <div className="grid gap-4 grid-cols-2 mb-6 border border-t-0 border-l-0 border-r-0 border-b-1">
                                        <label className="label">Tribal Jurisdiction of Company Formation</label>
                                        <p className="max-w-md font-semibold">{filing.company_info?.tribal_formation_name}</p>
                                    </div>

                                    <div className="grid gap-4 grid-cols-2 mb-6 border border-t-0 border-l-0 border-r-0 border-b-1">
                                        <label className="label">Name of the other Tribe</label>
                                        <p className="max-w-md font-semibold">{filing.company_info?.tribal_other_name}</p>
                                    </div>

                                    <div className="grid gap-4 grid-cols-2 mb-6 border border-t-0 border-l-0 border-r-0 border-b-1">
                                        <label className="label">Current Street Address</label>
                                        <p className="max-w-md font-semibold">{filing.company_info?.current_street_address}</p>
                                    </div>

                                    <div className="grid gap-4 grid-cols-2 mb-6 border border-t-0 border-l-0 border-r-0 border-b-1">
                                        <label className="label">Current City</label>
                                        <p className="max-w-md font-semibold">{filing.company_info?.current_city}</p>
                                    </div>

                                    <div className="grid gap-4 grid-cols-2 mb-6 border border-t-0 border-l-0 border-r-0 border-b-1">
                                        <label className="label">Current U.S. or U.S. Territory</label>
                                        <p className="max-w-md font-semibold">{filing.company_info?.current_country_name}</p>
                                    </div>

                                    <div className="grid gap-4 grid-cols-2 mb-6 border border-t-0 border-l-0 border-r-0 border-b-1">
                                        <label className="label">Current State</label>
                                        <p className="max-w-md font-semibold">{filing.company_info?.current_state_name}</p>
                                    </div>

                                    <div className="grid gap-4 grid-cols-2 mb-6 border border-t-0 border-l-0 border-r-0 border-b-1">
                                        <label className="label">Current ZIP</label>
                                        <p className="max-w-md font-semibold">{filing.company_info?.zip}</p>
                                    </div>
                                </Collapse.Content>
                            </Collapse.Details>

                            {/* Company Applicants */}
                            <Collapse.Details
                                icon="arrow"
                                className={`p-3 mb-3 border border-base-300 `}

                            >
                                <Collapse.Details.Title
                                    onClick={() => handleCollapseClick('companyApplicants')}
                                    className="text-xl font-medium mb-3">
                                    Company Applicants
                                </Collapse.Details.Title>
                                <Collapse.Content>
                                    {filing.company_applicants.map((applicant, index) => (
                                        <ReviewApplicants key={index} data={applicant} />
                                    ))}

                                </Collapse.Content>
                            </Collapse.Details>

                            {/* Beneficial Owners Applicants */}
                            <Collapse.Details
                                icon="arrow"
                                className={`p-3 mb-3 border `}
                            >
                                <Collapse.Details.Title
                                    onClick={() => handleCollapseClick('beneficialOwners')}
                                    className="text-xl font-medium mb-3">
                                    Beneficial Owners
                                </Collapse.Details.Title>
                                <Collapse.Content>
                                    {filing.beneficial_owners.map((owner, index) => (
                                        <ReviewApplicants key={index} data={owner} />
                                    ))}

                                </Collapse.Content>
                            </Collapse.Details>
                            <div className="flex justify-between mt-4">
                                <Button color="secondary" className="font-bold text-white btn btn-secondary" >
                                    Save
                                </Button>
                                
                                <Button
                                    color="primary"
                                    className="btn btn-primary font-bold text-white"
                                    tag="a"
                                    href={route('filing.submitFiling', { id: filing.id })}
                                >Save & Submit </Button>
                            </div>
                      </form>
                    </div>
                </div>
            </div>
        </AuthSidebarLayout>
    );
}