import Alert from "@/Components/Alert";
import AuthSidebarLayout from "@/Layouts/AuthSidebarLayout";
import InviteOwnerModal from "@/Pages/Partials/InviteOwnerModal";
import { PlusIcon } from "@heroicons/react/24/outline";
import { Head, usePage } from "@inertiajs/react";
import { useState } from "react";
import { Button, Table, Link, Input } from "react-daisyui";



export default function MembersIndex({ auth, members }) {
    const { messages } = usePage().props;

    const [searchTerm, setSearchTerm] = useState("");
    const [showInviteMemberModal, setShowInviteMemberModal] = useState(false);


    const handleSearchChange = (event) => {
        setSearchTerm(event.target.value.toLowerCase());
    }

    const filteredMembers = members.filter(member => {
        return (
            member.name?.toLowerCase().includes(searchTerm) ||
            member.email?.toLowerCase().includes(searchTerm) ||
            member.role?.toLowerCase().includes(searchTerm)
        );
    });

    console.log(members);


    return (
        <AuthSidebarLayout user={auth.user}>
            <Head title="Team" />
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
                        <div className="flex justify-between mb-3 items-center">
                            <h2 className="font-bold text-2xl">Team Members</h2>
                            <Button
                                color="primary"
                                className="font-bold text-white"
                                startIcon={<PlusIcon className="h-4 w-4" />}
                                onClick={() => setShowInviteMemberModal(true)}
                            >
                                Add Team Member
                            </Button>
                        </div>
                        <div className="flex justify-between mb-3 items-center">
                            <Input placeholder="Search Team Members" className="w-full" onChange={handleSearchChange} />

                        </div>
                        <div className="overflow-x-auto">
                            <Table size="lg">
                                <Table.Head>
                                    <span>Full Name</span>
                                    <span>Email</span>
                                    <span>Role</span>
                                    <span>Actions</span>
                                </Table.Head>

                                <Table.Body>
                                    {filteredMembers.map(member => (
                                        <Table.Row className="hover:bg-primary/10" key={member.id}>
                                            <span>{member.name}</span>
                                            <span>{member.email}</span>
                                            <span>
                                                {member.role === "Superadministrator" ? (
                                                    <span className="text-white font-bold badge badge-success">Super Admin</span>
                                                ) : (member.role === 'Administrator') ? (
                                                    <span className="text-white font-bold badge badge-info">Admin</span>
                                                ) : (member.role === 'User') ? (
                                                    <span className="text-white font-bold badge badge-neutral">User</span>
                                                ) : (member.status === 'pending') ? (
                                                    <span className="text-white font-bold badge badge-primary">Invited</span>
                                                ) : (member.status === 'expired') ? (
                                                    <span className="text-white font-bold badge badge-warning">Expired</span>
                                                ) : ''
                                                }

                                            </span>
                                            <span>
                                                {member.status === 'expired' ? (
                                                    // this works because we merged the array of invites with the team members  
                                                    <Link href={route('invite.resendInvite', member.id)} className="text-primary">Resend Invite</Link>
                                                ) : (member.status === 'active') ? (
                                                    <Link href={route('team.members.edit', member.id)} className="text-primary">Edit</Link>
                                                ) : ''}
                                            </span>
                                        </Table.Row>
                                    ))}


                                </Table.Body>
                            </Table>
                        </div>
                    </div>
                </div>
            </div>
            {showInviteMemberModal && (
                <InviteOwnerModal
                    show={showInviteMemberModal}
                    onClose={() => setShowInviteMemberModal(false)}
                    type="team_member"
                    title="Invite Team Member"
                />
            )}

        </AuthSidebarLayout>
    )
}