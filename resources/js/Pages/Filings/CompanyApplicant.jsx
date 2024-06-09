import AuthSidebarLayout from "@/Layouts/AuthSidebarLayout";
import { useEffect, useState } from "react";
import { useForm, usePage } from '@inertiajs/react';
import InputError from '@/Components/InputError';
import { Input, Button, Steps, Table } from "react-daisyui"
import { PencilSquareIcon, TrashIcon, PlusIcon } from "@heroicons/react/24/outline";
import Modal from "@/Components/Modal";
import { formatOwnerInfo } from "@/helpers/utils";
import ApplicantForm from "@/Pages/Partials/ApplicantForm";
import InviteOwnerModal from "@/Pages/Partials/InviteOwnerModal";
import Alert from "@/Components/Alert";


export default function CompanyApplicant({ auth, filing, filingTypes, status, applicants, teamApplicants, countries, states, tribes }) {


    const { messages } = usePage().props;

    const [searchTerm, setSearchTerm] = useState('');
    const [allApplicantsSearchTerm, setAllApplicantsSearchTerm] = useState('');
    const [filingApplicants, setFilingApplicants] = useState(applicants);
    const [allCompanyApplicants, setAllCompanyApplicants] = useState(teamApplicants || []);
    const [showApplicantForm, setShowApplicantForm] = useState(false);
    const [showAddApplicantModal, setShowAddApplicantModal] = useState(false);
    const [selectedApplicant, setSelectedApplicant] = useState(null);
    const [showInviteApplicantModal, setShowInviteApplicantModal] = useState(false);
    const [createApplicantForm, setCreateApplicantForm] = useState(false);
    // const [isExistingReportingCompany, setIsExistingReportingCompany] = useState(filing.company_info?.existing_reporting_company);
    const [isDisabled, setIsDisabled] = useState({
        foreignPooledInvestment: filing.company_info?.foreign_pooled_investment,
        existingReportingCompany: filing.company_info?.existing_reporting_company,

    });

    console.log(isDisabled);


    const handleEditApplicantForm = (applicant) => {
        setSelectedApplicant(applicant);
        setShowApplicantForm(true);
    }



    useEffect(() => {
        setFilingApplicants(applicants);
        setAllCompanyApplicants(teamApplicants);
    }, [applicants, teamApplicants, selectedApplicant]);



    const handleSearchChange = (event) => {
        setSearchTerm(event.target.value.toLowerCase());
    }

    const handleAllApplicantSearchChange = (event) => {
        setAllApplicantsSearchTerm(event.target.value.toLowerCase());
    }

    const resetApplicantForm = () => {
        setSelectedApplicant(null);
        setShowApplicantForm(false);
    };

    const resetCreateApplicantForm = () => {
        setCreateApplicantForm(false);
    };

    const handleInviteApplicant = () => {
        setShowAddApplicantModal(false);
        setShowInviteApplicantModal(true);
    }

    const handleCreateApplicant = () => {
        setShowAddApplicantModal(false);
        setCreateApplicantForm(true);
    }

    const filteredApplicants = filingApplicants?.filter(owner => {
        return (
            owner.first_name?.toLowerCase().includes(searchTerm) ||
            owner.last_name?.toLowerCase().includes(searchTerm) ||
            owner.fincen_id?.includes(searchTerm) ||
            owner.email?.toLowerCase().includes(searchTerm)
        );
    });

    const filteredAllApplicants = allCompanyApplicants?.filter(owner => {
        return (
            owner.first_name?.toLowerCase().includes(allApplicantsSearchTerm) ||
            owner.last_name?.toLowerCase().includes(allApplicantsSearchTerm) ||
            owner.fincen_id?.includes(allApplicantsSearchTerm) ||
            owner.email?.toLowerCase().includes(allApplicantsSearchTerm)
        );
    });

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
                            <Steps.Step color="primary">Company Information</Steps.Step>
                            <Steps.Step color="primary">Company Applicants</Steps.Step>
                            <Steps.Step >Beneficial Owners</Steps.Step>
                            <Steps.Step >Review & Submit</Steps.Step>
                        </Steps>
                    </div>
                    <div className="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                        <div className="flex justify-between mb-3 items-center">
                            <h2 className="font-bold text-2xl">
                                Company Applicants
                            </h2>
                            <Button
                                tag="a"
                                color="primary"
                                className="font-bold text-white"
                                startIcon={<PlusIcon
                                    className="h-4 w-4" />}
                                onClick={() => setShowAddApplicantModal(true)}
                                // disabled if any prop in isDisabled is true
                                disabled={Object.values(isDisabled).some((val) => val === true)}
                            >
                                Add Applicant to Filing
                            </Button>
                        </div>
                        <div className="flex justify-between mb-3 items-center">
                            <Input placeholder="Search Filings" className="w-full" onChange={handleSearchChange} />
                        </div>
                        <div className="overflow-x-auto">
                            <Table size="lg" >
                                <Table.Head>
                                    <span className="text-lg text-gray-800">Applicant Last Name</span>
                                    <span className="text-lg text-gray-800">Applicant First Name</span>
                                    <span className="text-lg text-gray-800">Fincen ID</span>
                                    <span className="text-lg text-gray-800">Actions</span>
                                </Table.Head>

                                <Table.Body>
                                    {/* if there are no filtered applicants display an empty row */}
                                    {/* if any prop in isDisabled is true, then display a tr with the reason it's disabled */}


                                    {isDisabled.existingReportingCompany ? (
                                        <tr>
                                            <td colSpan="4" className="text-center text-gray-800 font-bold">
                                                Existing Reporting Company. Do not add company applicants.
                                            </td>
                                        </tr>
                                    ) : (isDisabled.foreignPooledInvestment) ? (
                                        <tr>
                                            <td colSpan="4" className="text-center text-gray-800 font-bold">
                                                Foriegn Pooled Investment. Do not add company applicants.
                                            </td>
                                        </tr>
                                    ) : (filteredApplicants.length === 0) ? (
                                        <tr>
                                            <td colSpan="4" className="text-center text-gray-800">
                                                No company applicants added to this filing.
                                            </td>
                                        </tr>
                                    ) : filteredApplicants.map((applicant) => (
                                        <tr key={applicant.id}>
                                            <td className="w-1/4">{applicant.last_name}</td>
                                            <td className="w-1/4">{applicant.first_name}</td>
                                            <td className="w-1/4">{applicant.fincen_id}</td>
                                            <td className="flex flex-col md:flex-row space-y-2 md:space-y-0 md:space-x-2 items-center">
                                                <Button
                                                    color="primary"
                                                    className="text-xs sm:text-sm md:text-xs w-full md:w-auto md:btn-sm font-bold text-white"
                                                    startIcon={<PencilSquareIcon className="lg:h-4 lg:w-4 md:h-2 md:w-2" />}
                                                    onClick={() => handleEditApplicantForm(applicant)}
                                                >
                                                    Edit
                                                </Button>
                                                <Button
                                                    tag="a"
                                                    href={route('filing.applicants.removeFromFiling', { id: filing.id, applicant_id: applicant.id })}
                                                    color="secondary"
                                                    className="text-xs sm:text-sm md:text-xs w-full md:w-auto md:btn-sm font-bold text-white"
                                                    startIcon={<TrashIcon className="lg:h-4 lg:w-4 md:h-2 md:w-2" />}
                                                >
                                                    Remove
                                                </Button>
                                            </td>
                                        </tr>
                                    ))}

                                </Table.Body>
                            </Table>
                        </div>
                        <div className="flex justify-between mt-6">
                            <Button
                                tag="a"
                                href={route('filing.company_info.show', filing.id)}
                                color="neutral"
                                className="font-bold text-white"
                            >
                                Back
                            </Button>
                            <Button
                                tag="a"
                                href={route('filing.index')}
                                color="accent"
                                className="font-bold text-white"
                            >
                                Save & Exit
                            </Button>

                            <Button
                                tag="a"
                                href={route('filing.owners.show', filing.id)}
                                color="primary"
                                className="font-bold text-white"
                            >
                                Save & Continue
                            </Button>

                        </div>
                    </div>
                    {messages?.success && (

                        <Alert type="success" message={messages.success} />
                    )}


                    {showApplicantForm && (
                        <ApplicantForm
                            key={selectedApplicant.id}
                            applicant={selectedApplicant}
                            filing={filing}
                            title={`Edit ${selectedApplicant.first_name} ${selectedApplicant.last_name}`}
                            countries={countries}
                            states={states}
                            tribes={tribes}
                            routeInfo={{
                                name: 'filing.applicants.update',
                                type: 'post',
                                params: { id: filing.id, applicant_id: selectedApplicant.id },
                            }}
                            resetForm={resetApplicantForm}
                        />
                    )}

                    {createApplicantForm && (
                        <ApplicantForm
                            title={`Create New Company Applicant`}
                            countries={countries}
                            states={states}
                            tribes={tribes}
                            routeInfo={{
                                name: 'filing.applicants.store',
                                type: 'post',
                                params: { id: filing.id },
                            }}
                            resetForm={resetCreateApplicantForm}
                        />
                    )}


                </div>
            </div>


            <Modal show={showAddApplicantModal} onClose={() => setShowAddApplicantModal(false)} maxWidth="2xl" >
                <div className="bg-white p-6">
                    <h2 className="font-bold text-gray-900 text-3xl mb-3">
                        Add Owner to Filing
                    </h2>
                    <div className="grid mb-6">
                        <Input
                            placeholder="Search all team Applicants by name or FinCEN ID"
                            className="w-full input input-bordered input-secondary"
                            bordered={true}
                            onChange={handleAllApplicantSearchChange}
                        />
                    </div>
                    {/* Scrollable Container */}
                    <div className="grid mb-6 max-h-96 overflow-y-auto border border-gray-300">
                        {filteredAllApplicants.map((applicant) => (
                            <div key={applicant.id} className="card w-full bg-base-100 shadow-md border border-gray-200">
                                <div className="card-body p-2">
                                    <div className="flex justify-between items-center">
                                        <div>
                                            <p>{`${applicant?.first_name ?? ""} ${applicant?.last_name ?? ""}`.trim()} {applicant?.fincen_id ? `| ${applicant.fincen_id}` : ''}</p>
                                        </div>
                                        <div>
                                            <Button
                                                tag="a"
                                                href={route('filing.applicants.addToFiling', { id: filing.id, applicant_id: applicant.id })}
                                                color="primary"
                                                className="font-bold text-white"
                                            >
                                                Add to Filing
                                            </Button>
                                        </div>
                                    </div>
                                </div>
                                </div>
                        ))}
                    </div>
                    <div className="flex justify-between mt-8">
                        <Button
                            color="neutral"
                            onClick={() => setShowAddApplicantModal(false)}
                            className="font-bold text-white"
                        >
                            Cancel
                        </Button>
                        <Button
                            color="accent"
                            onClick={() => handleCreateApplicant()}
                            className="font-bold text-white"
                        >
                            Add Applicant Manually
                        </Button>
                        <Button
                            color="primary"
                            onClick={() => handleInviteApplicant()}
                            className="font-bold text-white"
                        >
                            Invite Applicant
                        </Button>
                    </div>
                </div>
            </Modal>

            <InviteOwnerModal
                show={showInviteApplicantModal}
                onClose={() => setShowInviteApplicantModal(false)}
            />
        </AuthSidebarLayout>

    )
}

// FAQ: who is a company applicant?
// Only reporting companies created or registered on or after January 1, 2024, will need to report their company applicants.

// A company that must report its company applicants will have only up to two individuals who could qualify as company applicants:

// The individual who directly files the document that creates or registers the company; and
// If more than one person is involved in the filing, the individual who is primarily responsible for directing or controlling the filing.
// Was company created or registered on or after January 1, 2024?
// if yes
// Identify the individual who directly filed the document that created or registered the company with the secretary of state or other state or tribal official.
// Was more than one individual involved in the filing of the company's creation or first registration document?
// if yes
// Identify the individual who is primarily responsible for directing or controlling the filing.