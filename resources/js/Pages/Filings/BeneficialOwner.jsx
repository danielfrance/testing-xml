import AuthSidebarLayout from "@/Layouts/AuthSidebarLayout";
import { useEffect, useState } from "react";
import { useForm, usePage } from '@inertiajs/react';
import InputError from '@/Components/InputError';
import { Input, Button, Steps, Table } from "react-daisyui"
import { PencilSquareIcon, TrashIcon, PlusIcon } from "@heroicons/react/24/outline";
import Modal from "@/Components/Modal";
import { formatOwnerInfo } from "@/helpers/utils";
import OwnerForm from "@/Pages/Partials/OwnerForm";
import InviteOwnerModal from "../Partials/InviteOwnerModal";
import Alert from "@/Components/Alert";



export default function BeneficialOwner({ auth, filing, beneficialOwners, allOwners, countries, states, tribes }) {

    const { messages } = usePage().props;

    const [searchTerm, setSearchTerm] = useState('');
    const [allOwnersSearchTerm, setAllOwnersSearchTerm] = useState('');
    const [filingOwners, setFilingOwners] = useState(beneficialOwners);
    const [allBeneficialOwners, setAllBeneficialOwners] = useState(allOwners || []);
    const [showOwnerForm, setShowOwnerForm] = useState(false);
    const [showAddOwnerModal, setShowAddOwnerModal] = useState(false);
    const [selectedOwner, setSelectedOwner] = useState(null);
    const [showInviteOwnerModal, setShowInviteOwnerModal] = useState(false);
    const [createOwnerForm, setCreateOwnerForm] = useState(false);

    const handleEditOwnerForm = (applicant) => {
        setSelectedOwner(applicant);
        setShowOwnerForm(true);
    }



    useEffect(() => {
        setFilingOwners(beneficialOwners);
        setAllBeneficialOwners(allOwners);
    }, [beneficialOwners, allOwners, selectedOwner]);



    const handleSearchChange = (event) => {
        setSearchTerm(event.target.value.toLowerCase());
    }

    const handleAllOwnerSearchChange = (event) => {
        setAllOwnersSearchTerm(event.target.value.toLowerCase());
    }

    const resetOwnerForm = () => {
        setSelectedOwner(null);
        setShowOwnerForm(false);
    };

    const resetCreateOwnerForm = () => {
        setCreateOwnerForm(false);
    };

    const handleInviteOwner = () => {
        setShowAddOwnerModal(false);
        setShowInviteOwnerModal(true);
    }

    const handleCreateOwner = () => {
        setShowAddOwnerModal(false);
        setCreateOwnerForm(true);
    }

    const filteredOwners = filingOwners?.filter(owner => {
        return (
            owner.first_name?.toLowerCase().includes(searchTerm) ||
            owner.last_name?.toLowerCase().includes(searchTerm) ||
            owner.fincen_id?.includes(searchTerm) ||
            owner.email?.toLowerCase().includes(searchTerm)
        );
    });

    const filteredAllOwners = allBeneficialOwners?.filter(owner => {
        return (
            owner.first_name?.toLowerCase().includes(allOwnersSearchTerm) ||
            owner.last_name?.toLowerCase().includes(allOwnersSearchTerm) ||
            owner.fincen_id?.includes(allOwnersSearchTerm) ||
            owner.email?.toLowerCase().includes(allOwnersSearchTerm)
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
                            <Steps.Step color="primary">Beneficial Owners</Steps.Step>
                            <Steps.Step >Review & Submit</Steps.Step>
                        </Steps>
                    </div>
                    <div className="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                        <div className="flex justify-between mb-3 items-center">
                            <h2 className="font-bold text-2xl">
                                Filing Beneficial Owners
                            </h2>
                            <Button
                                tag="a"
                                color="primary"
                                className="font-bold text-white"
                                startIcon={<PlusIcon
                                    className="h-4 w-4" />}
                                onClick={() => setShowAddOwnerModal(true)}
                            >
                                Add Owner to Filing
                            </Button>
                        </div>
                        <div className="flex justify-between mb-3 items-center">
                            <Input placeholder="Search Filings" className="w-full" onChange={handleSearchChange} />
                        </div>
                        <div className="overflow-x-auto">
                            <Table size="lg" >
                                <Table.Head>
                                    <span className="text-lg text-gray-800">Owner Last Name</span>
                                    <span className="text-lg text-gray-800">Owner First Name</span>
                                    <span className="text-lg text-gray-800">Fincen ID</span>
                                    <span className="text-lg text-gray-800">Actions</span>
                                </Table.Head>

                                <Table.Body>
                                    {filteredOwners.map(owner => (
                                        <tr key={owner.id}>
                                            <td className="w-1/4">{owner.last_name}</td>
                                            <td className="w-1/4">{owner.first_name}</td>
                                            <td className="w-1/4">{owner.fincen_id}</td>
                                            <td className="flex flex-col md:flex-row space-y-2 md:space-y-0 md:space-x-2 items-center">
                                                <Button
                                                    color="primary"
                                                    className="text-xs sm:text-sm md:text-xs w-full md:w-auto md:btn-sm font-bold text-white"
                                                    startIcon={<PencilSquareIcon className="lg:h-4 lg:w-4 md:h-2 md:w-2" />}
                                                    onClick={() => handleEditOwnerForm(owner)}
                                                >
                                                    Edit
                                                </Button>
                                                <Button
                                                    tag="a"
                                                    href={route('filing.owners.removeFromFiling', { id: filing.id, owner_id: owner.id })}
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
                        <div className="flex justify-between mt-4">
                            <Button
                                tag="a"
                                href={route('filing.applicants.show', filing.id)}
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
                                href={route('filing.review', filing.id)}
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


                    {showOwnerForm && (
                        <OwnerForm
                            key={selectedOwner.id} 
                            owner={selectedOwner}
                            filing={filing}
                            title={`Edit ${selectedOwner.first_name} ${selectedOwner.last_name}`}
                            countries={countries}
                            states={states}
                            tribes={tribes}
                            routeInfo={{
                                name: 'filing.owners.update',
                                type: 'post',
                                params: { id: filing.id, owner_id: selectedOwner.id },
                            }}
                            resetForm={resetOwnerForm}
                        />
                    )}

                    {createOwnerForm && (
                        <OwnerForm
                            title={`Create new Owner`}
                            countries={countries}
                            states={states}
                            tribes={tribes}
                            routeInfo={{
                                name: 'filing.owners.store',
                                type: 'post',
                                params: { id: filing.id },

                            }}
                            resetForm={resetCreateOwnerForm}
                        />
                    )}

                </div>
            </div>


            <Modal show={showAddOwnerModal} onClose={() => setShowAddOwnerModal(false)} maxWidth="2xl" >
                <div className="bg-white p-6">
                    <h2 className="font-bold text-gray-900 text-3xl mb-3">
                        Add Owner to Filing
                    </h2>
                    <div className="grid mb-6">
                        <Input
                            placeholder="Search all team Applicants by name or FinCEN ID"
                            className="w-full input input-bordered input-secondary"
                            bordered={true}
                            onChange={handleAllOwnerSearchChange}
                        />
                    </div>
                    {/* Scrollable Container */}
                    <div className="grid mb-6 max-h-96 overflow-y-auto border border-gray-300">
                        {filteredAllOwners.map((owner) => (
                            <div key={owner.id} className="card w-full bg-base-100 shadow-md border border-gray-200">
                                <div className="card-body p-2">
                                    <div className="flex justify-between items-center">
                                        <div>
                                            <p>{`${owner?.first_name ?? ""} ${owner?.last_name ?? ""}`.trim()} {owner?.fincen_id ? `| ${owner.fincen_id}` : ''}</p>
                                        </div>
                                        <div>
                                            <Button
                                                tag="a"
                                                href={route('filing.owners.addToFiling', { id: filing.id, owner_id: owner.id })}
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
                            onClick={() => setShowAddOwnerModal(false)}
                            className="font-bold text-white"
                        >
                            Cancel
                        </Button>
                        <Button
                            color="accent"
                            onClick={() => handleCreateOwner()}
                            className="font-bold text-white"
                        >
                            Add Owner Manually
                        </Button>
                        <Button
                            color="primary"
                            onClick={() => handleInviteOwner()}
                            className="font-bold text-white"
                        >
                            Invite Owner
                        </Button>
                    </div>
                </div>
            </Modal>

            <InviteOwnerModal
                show={showInviteOwnerModal}
                onClose={() => setShowInviteOwnerModal(false)}
            />
        </AuthSidebarLayout>
    );
}