import AuthSidebarLayout from "@/Layouts/AuthSidebarLayout";
import { Head, usePage } from "@inertiajs/react";
import { Button, Table, Link, Input } from "react-daisyui";
import { ArrowPathIcon, PlusIcon } from "@heroicons/react/24/outline";
import Modal from "@/Components/Modal";
import { useState } from "react";
import InviteOwnerModal from "../Partials/InviteOwnerModal";
import Alert from "@/Components/Alert";

export default function OwnersIndex({ auth, owners, invitees }) {

    const { messages } = usePage().props

    const [creationType, setCreationType] = useState(null);
    const [showCreateOwnerModal, setShowCreateOwnerModal] = useState(false);
    const [showInviteOwnerModal, setShowInviteOwnerModal] = useState(false);
    const [searchTerm, setSearchTerm] = useState('');
    const [searchInvitedTerm, setSearchInvitedTerm] = useState('');

    const handleCreateOwnerSelection = () => {
        setShowCreateOwnerModal(false);

        if (creationType === 'manual') {
            // redirect user to owners.create
            window.location.href = '/owners/create';
        }
        if (creationType === 'owner') {
            setShowInviteOwnerModal(true);
        }
    }

    const handleCloseInviteOwnerModal = () => {
        setShowInviteOwnerModal(false);
        setCreationType(null);
    }

    const handleSearchChange = (event) => {
        setSearchTerm(event.target.value.toLowerCase());
    }

    const handleSearchInvitedChange = (event) => {
        setSearchInvitedTerm(event.target.value.toLowerCase());
    }


    const filteredOwners = owners?.filter(owner => {
        return (
            owner.first_name?.toLowerCase().includes(searchTerm) ||
            owner.last_name?.toLowerCase().includes(searchTerm) ||
            owner.email?.toLowerCase().includes(searchTerm) ||
            owner.fincen_id?.includes(searchTerm) ||
            (searchTerm.toLowerCase().includes("verified") && owner.info_verified_at != null)
        );
    });

    const filteredInvitees = invitees?.filter(invitee => {
        return (
            invitee.name?.toLowerCase().includes(searchInvitedTerm) ||
            invitee.email?.toLowerCase().includes(searchInvitedTerm) ||
            invitee.status?.includes(searchInvitedTerm)
        );
    });


    return (
        <AuthSidebarLayout user={auth.user}>
            <Head title="Owners" />
            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="mb-5">
                        {messages?.success && (

                            <Alert type="success" message={messages.success} />
                        )}
                        {messages?.error && (

                            <Alert type="danger" message={messages.error} />
                        )}
                    </div>
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg px-2 py-3">
                        <div role="tablist" className="tabs tabs-lifted tabs-lg">
                            <input
                                type="radio"
                                name="owners_tabs"
                                role="tab"
                                className="tab"
                                aria-label="All Owners"
                                defaultChecked

                            />
                            <div className="tab-content bg-base-100 borer-base-300 rounded-box p-6">
                                <div className="flex justify-between mb-3 items-center">
                                    <h2 className="font-bold text-2xl">Owners</h2>
                                    <Button
                                        color="primary"
                                        className="font-bold text-white"
                                        startIcon={<PlusIcon className="h-4 w-4" />}
                                        onClick={() => setShowCreateOwnerModal(true)}
                                    >
                                        Add Owner
                                    </Button>
                                </div>
                                <div className="flex justify-between mb-3 items-center">
                                    <Input placeholder="Search Owners" className="w-full" onChange={handleSearchChange} />

                                </div>
                                <div className="overflow-x-auto">
                                    <div className="overflow-x-auto">
                                        <Table size="lg">
                                            <Table.Head>
                                                <span>Full Name</span>
                                                <span>Email</span>
                                                <span>Verified</span>
                                                <span>Actions</span>
                                            </Table.Head>

                                            <Table.Body>
                                                {filteredOwners.map(owner => (
                                                    <Table.Row className="hover:bg-primary/10" key={owner.id}>
                                                        <span>{owner.first_name} {owner.last_name}</span>
                                                        <span>{owner.email}</span>
                                                        <span>
                                                            {owner.info_verified_at ? (
                                                                <div className="badge badge-success">Verified</div>
                                                            ) : (
                                                                <div className="badge badge-default">Not Verified</div>
                                                            )}
                                                        </span>
                                                        <span>
                                                            <Link href={route('owners.edit', owner.id)} className="text-primary">Edit</Link>
                                                        </span>
                                                    </Table.Row>
                                                ))}


                                            </Table.Body>
                                        </Table>
                                    </div>
                                </div>
                            </div>
                            <input
                                type="radio"
                                name="owners_tabs"
                                role="tab"
                                className="tab"
                                aria-label="Invited Owners"
                            />
                            <div className="tab-content bg-base-100 borer-base-300 rounded-box p-6">
                                <div className="flex justify-between mb-3 items-center">
                                    {/* TODO: add tooltip. invited owners are not "owners", they are actually "invites".  when invitee accepts the invite, an owner record will be saved. invite will be removed and owner record will appear in the All Owners table */}
                                    <h2 className="font-bold text-2xl">Invited Owners</h2>
                                    <Button
                                        color="primary"
                                        className="font-bold text-white"
                                        startIcon={<PlusIcon className="h-4 w-4" />}
                                        onClick={() => setShowCreateOwnerModal(true)}
                                    >
                                        Add Owner
                                    </Button>
                                </div>
                                <div className="flex justify-between mb-3 items-center">
                                    <Input placeholder="Search Invited Owners" className="w-full" onChange={handleSearchInvitedChange} />
                                </div>
                                <div className="overflow-x-auto">
                                    <div className="overflow-x-auto">
                                        <Table size="lg">
                                            <Table.Head>
                                                <span>Full Name</span>
                                                <span>Email</span>
                                                <span>Status</span>
                                                <span>Actions</span>
                                            </Table.Head>

                                            <Table.Body>
                                                {filteredInvitees.map(invite => (
                                                    <Table.Row className="hover:bg-primary/10" key={invite.id}>
                                                        <span>{invite.name}</span>
                                                        <span>{invite.email}</span>
                                                        <span>
                                                            {invite.status === 'pending' ? (
                                                                <div className="badge badge-info">Pending</div>
                                                            ) : (invite.status === 'expired') ? (
                                                                <div className="badge badge-error">Expired</div>
                                                            ) : (invite.status === 'accepted') ? (
                                                                <div className="badge badge-success">Accepted</div>
                                                            ) : (invite.status === 'requested') ? (
                                                                <div className="badge badge-warning">Requested New Token</div>
                                                            ) : (
                                                                <div className="badge badge-default">Unknown</div>

                                                            )}
                                                        </span>
                                                        <span className="flex justify-between">

                                                            <Link href={route('invite.resendInvite', invite.id)} className="text-primary ml-2">Resend</Link>

                                                        </span>
                                                    </Table.Row>
                                                ))}


                                            </Table.Body>
                                        </Table>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            <Modal show={showCreateOwnerModal} maxWidth="2xl" closeable={true} onClose={() => setShowCreateOwnerModal(false)}>
                <div className="bg-white overflow-hidden p-6">
                    <h2 className="font-bold text-gray-900 text-3xl mb-3">
                        Create New Owner
                    </h2>
                    <h3 className=" text-gray-900 text-lg mb-3">
                        Before you begin, you will need the following information:
                    </h3>
                    <ul className="list-disc">
                        <li>A FinCEN ID</li>
                        <div className="divider">OR</div>

                        <li>Full Legal Name</li>
                        <li>Date of Birth</li>
                        <li>Current Address</li>
                        <li>Identifying Documents like a Passport or Drivers License</li>
                    </ul>

                    <h3 className="mt-3 text-gray-900 text-lg mb-3">
                        Start by selecting how a new owner will be created:
                    </h3>
                    <div className="grid grid-cols-2 gap-4 mt-6">
                        <div className="border border-gray-200 rounded-lg p-4 text-center content-center">
                            <h4 className="font-bold text-gray-900 text-lg">
                                Enter Manually
                            </h4>
                            <p className="text-gray-700">
                                You will enter the Owner's Info.
                            </p>
                            <Button
                                onClick={() => setCreationType('manual')}
                                className={`btn  text-white font-bold mt-4 ${creationType === 'manual' ? 'btn-primary' : 'btn-neutral'}`}>
                                Select
                            </Button>
                        </div>
                        <div className="border border-gray-200 rounded-lg p-4 text-center content-center">
                            <h4 className="font-bold text-gray-900 text-lg">
                                Invite Owner
                            </h4>
                            <p className="text-gray-700">
                                Send to the Owner to fill out.
                            </p>
                            <Button
                                onClick={() => setCreationType('owner')}
                                className={`btn text-white font-bold mt-4 ${creationType === 'owner' ? 'btn-primary' : 'btn-neutral'}`}>
                                Select
                            </Button>
                        </div>
                    </div>
                    <div className="flex justify-end mt-4">
                        <Button
                            className="btn btn-neutral"
                            disabled={creationType === null}
                            onClick={handleCreateOwnerSelection}
                        >
                            Create Owner
                        </Button>
                    </div>
                </div>
            </Modal>

            {showInviteOwnerModal && (
                <InviteOwnerModal
                    show={showInviteOwnerModal}
                    onClose={handleCloseInviteOwnerModal}
                    type="owner"
                />
            )}


        </AuthSidebarLayout>
    );
}
