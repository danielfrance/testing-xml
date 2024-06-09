import { usePage } from '@inertiajs/react';
import OwnerForm from "@/Pages/Partials/OwnerForm";
import Alert from "@/Components/Alert";
import InvitedLayout from "@/Layouts/InvitedLayout";

export default function InvitedOwner({ invite, countries, states, tribes, }) {

    const { messages } = usePage().props

    // TODO: add a "I verify that all information is correct" checkbox

    return (
        <InvitedLayout >
            {messages?.success && (

                <Alert type="success" message={messages.success} />
            )}
            {messages?.error && (

                <Alert type="warning" message={messages.error} />
            )}
            <OwnerForm
                title={`Create Your Owner Profile ${invite?.name}`}
                countries={countries}
                states={states}
                tribes={tribes}
                invite={invite}
                routeInfo={{
                    name: 'invite.owner.store',
                    type: 'post',
                    params: { team_id: invite?.team_id, token: invite?.token }
                }}
            />

        </InvitedLayout>
    )
}