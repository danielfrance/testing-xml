import AuthSidebarLayout from '@/Layouts/AuthSidebarLayout';
import FilingInformation from "./FilingInformation";
import CompanyInformation from "./__CompanyInformation";
import { Steps } from 'react-daisyui';

export default function Edit({auth, company, filingTypes, status}) {
    return (
        <AuthSidebarLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Edit Reporting Company</h2>}
        >
            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                    <div className="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                        <Steps vertical={false} className='w-full'>
                            <Steps.Step color='primary'>Filing Info</Steps.Step>
                            <Steps.Step>Company Information</Steps.Step>
                            <Steps.Step>Company Applicants</Steps.Step>
                            <Steps.Step>Beneficial Owners</Steps.Step>
                            <Steps.Step title="Review">Review & Submit</Steps.Step>
                        </Steps>
                    </div>
                    <div className="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                        <FilingInformation />
                    </div>
                    <div className="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                        <CompanyInformation />
                    </div>
                </div>
            </div>
        </AuthSidebarLayout>
    );
}