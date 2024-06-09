import { Link, usePage } from "@inertiajs/react";
import Alert from "@/Components/Alert";
import InvitedLayout from "@/Layouts/InvitedLayout";


export default function ExpiredInvite({ invite }) {
    console.log(invite);
    const { messages } = usePage().props

    return (
        <InvitedLayout>
            <div className="mb-5">
                {messages?.success && (

                    <Alert type="success" message={messages.success} />
                )}
                {messages?.error && (

                    <Alert type="danger" message={messages.error} />
                )}
            </div>
            <div className="grid text-center">
                <h1 className="text-2xl font-bold mb-4">Link Expired</h1>
                <p className="mb-4 text-lg">The magic link you used has expired. Please request a new link to access the form.</p>
                <Link href={route('invite.requestNewToken', invite.token)} className="btn btn-primary mt-3 text-white">Request New Link</Link>
            </div>
        </InvitedLayout>
    )
}