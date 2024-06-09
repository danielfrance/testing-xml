import Guest from "@/Layouts/GuestLayout";
import { Link, usePage } from "@inertiajs/react";
import Alert from "@/Components/Alert";

export default function CompletedProfile() {
    const { messages } = usePage().props

    return (
        <Guest>
            <div className="min-h-screen flex items-center justify-center ">

                <div className="bg-white p-8 rounded w-full max-w-lg text-center border shadow-lg">
                    <div className="mb-5">
                        {messages?.success && (

                            <Alert type="success" message={messages.success} />
                        )}
                        {messages?.error && (

                            <Alert type="danger" message={messages.error} />
                        )}
                    </div>
                    <h1 className="text-2xl font-bold mb-4">Profile Completed</h1>
                    <p className="mb-4 text-lg">You have already completed your profile. If the team has any questions they will reach out.</p>

                </div>
            </div>
        </Guest>
    )
}