import AuthSidebarLayout from "@/Layouts/AuthSidebarLayout";
import { usePage } from '@inertiajs/react';
import OwnerForm from "../Partials/OwnerForm";
import Alert from "@/Components/Alert";

export default function OwnerCreate({ auth, countries, states, tribes, }) {

    const { messages } = usePage().props

    return (
        <AuthSidebarLayout
            user={auth.user}
        >
            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                    {messages?.success && (

                        <Alert type="success" message={messages.success} />
                    )}

                    <OwnerForm
                        title={`Create new Owner`}
                        countries={countries}
                        states={states}
                        tribes={tribes}
                        routeInfo={{
                            name: 'owners.store',
                            type: 'post',
                        }}
                    />
                    
                </div>
            </div>

        </AuthSidebarLayout>
    )
}