import AuthSidebarLayout from "@/Layouts/AuthSidebarLayout";
import Alert from "@/Components/Alert";
import { usePage } from "@inertiajs/react";
import MemberForm from "./Partials/MemberForm";

export default function MemberEdit({ auth, member, roles }) {
    const { messages } = usePage().props;

    return (
        <AuthSidebarLayout user={auth.user}>
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
                    <h2 className="font-bold text-2xl">Edit Team Member</h2>
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg px-2 py-3">
                        <div className="flex justify-between mb-3 items-center">
                            <div className="w-full">
                                <MemberForm
                                    key={member.id}
                                    member={member}
                                    roles={roles}
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthSidebarLayout>
    )
}