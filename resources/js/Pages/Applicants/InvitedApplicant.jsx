import { usePage } from '@inertiajs/react';
import Alert from "@/Components/Alert";
import ApplicantForm from '@/Pages/Partials/ApplicantForm';
import InvitedLayout from '@/Layouts/InvitedLayout';

export default function InvitedApplicant({ invite, countries, states, tribes, }) {

    const { messages } = usePage().props

    return (
        <InvitedLayout >
            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                    {messages?.success && (

                        <Alert type="success" message={messages.success} />
                    )}
                    {messages?.error && (

                        <Alert type="warning" message={messages.error} />
                    )}

                    <ApplicantForm
                        title={`Create Your Applicant Profile ${invite?.name}`}
                        countries={countries}
                        states={states}
                        tribes={tribes}
                        routeInfo={{
                            name: 'invite.applicant.store',
                            type: 'post',
                            params: { team_id: invite?.team_id, token: invite?.token }
                        }}
                    />

                </div>
            </div>

        </InvitedLayout>
    )
}