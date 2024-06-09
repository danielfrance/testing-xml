import AuthSidebarLayout from "@/Layouts/AuthSidebarLayout";
import { usePage } from '@inertiajs/react';
import Alert from "@/Components/Alert";
import ApplicantForm from "../Partials/ApplicantForm";

export default function ApplicantEdit({ auth, applicant, countries, states, tribes }) {

    const { messages } = usePage().props;

    const handleResetForm = () => {
        // Reset form fields
        setTimeout(() => {
            const element = document.getElementById('mainContainer');

            if (element) {
                element.scrollIntoView({ behavior: 'smooth' });
            }
        }, 10)
    }

    return (
        <AuthSidebarLayout
            user={auth.user}
        >
            <div id="mainContainer" className="py-12 mainContainer">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                    {messages?.success && (

                        <Alert type="success" message={messages.success} />
                    )}
                    <ApplicantForm
                        title={`Edit Company Applicant: ${applicant.first_name} ${applicant.last_name}`}
                        applicant={applicant}
                        countries={countries}
                        states={states}
                        tribes={tribes}
                        routeInfo={{
                            name: 'applicants.update',
                            type: 'post',
                            params: { id: applicant.id }
                        }}
                        resetForm={handleResetForm}
                    />

                </div>
            </div>

        </AuthSidebarLayout>
    )
}