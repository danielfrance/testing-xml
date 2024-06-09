import { useState } from 'react';
import AuthSidebarLayout from '@/Layouts/AuthSidebarLayout';
import FilingInformation from "./FilingInformation";
import CompanyInformation from "./__CompanyInformation";
import { Steps } from 'react-daisyui';
import BeneficialOwner from './BeneficialOwner';
import CompanyApplicant from './CompanyApplicant';

export default function Create({ auth, filingType }) {
    const [currentStep, setCurrentStep] = useState(filingType === 'initial_report' ? 2 : 1);

    const handleStepChange = (step) => {
        if (step <= currentStep) {
            setCurrentStep(step);
        }
    };
    console.log(currentStep);

    return (
        <AuthSidebarLayout
            user={auth.user}
            
        >
            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                    <div className="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                        <Steps
                            className="w-full steps"
                            vertical={false}
                            current={currentStep} onChange={handleStepChange}>
                            <Steps.Step
                                color={currentStep >= 1 ? 'primary' : ''}>Filing Info</Steps.Step>
                            <Steps.Step
                                color={currentStep >= 2 ? 'primary' : ''} >Company Information</Steps.Step>
                            <Steps.Step
                                color={currentStep >= 3 ? 'primary' : ''}>Company Applicants</Steps.Step>
                            <Steps.Step
                                color={currentStep >= 4 ? 'primary' : ''}
                            >Beneficial Owners</Steps.Step>
                            <Steps.Step
                                color={currentStep >= 5 ? 'primary' : ''}
                                title="Review">Review & Submit</Steps.Step>
                        </Steps>
                    </div>
                    {currentStep == 1 && (
                        <div className="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                            <FilingInformation filingType={filingType} />
                        </div>
                    )}
                    {currentStep == 2 && (
                        <div className="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                            <CompanyInformation />
                        </div>
                    )}
                    {currentStep == 3 && (
                        <div className="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                            <CompanyApplicant />
                        </div>
                    )}
                </div>
            </div>
        </AuthSidebarLayout>
    );
}