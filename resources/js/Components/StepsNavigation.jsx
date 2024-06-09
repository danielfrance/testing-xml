import { Steps } from "react-daisyui";
import { Link } from '@inertiajs/react';


export default function StepsNavigation({ id, currentStep }) {
    return (
        <Steps
            className="w-full steps"
            vertical={false}
        >
            <Steps.Step color="primary">
                <Link href={route('filing.show', { id: id })}>Filing Info</Link>
            </Steps.Step>
            <Steps.Step>
                <Link href={route('filing.company_info.edit', { id: id })} > Company Information </Link>
            </Steps.Step>
            <Steps.Step>
                <Link>Company Applicants</Link>
            </Steps.Step>
            <Steps.Step >
                <Link>Beneficial Owners</Link>
            </Steps.Step>
            <Steps.Step >
                <Link>Review & Submit</Link>
            </Steps.Step>
        </Steps>
    )
}