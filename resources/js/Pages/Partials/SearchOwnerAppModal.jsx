import Modal from "@/Components/Modal"
import { Input, Button } from "react-daisyui"


export default function SearchOwnerAppModal({ auth, company, filingTypes, status }) {

    return (
        <Modal show={showAddApplicantModal} onClose={() => setShowAddApplicantModal(false)} maxWidth="2xl" >
            <div className="bg-white p-6">
                <h2 className="font-bold text-gray-900 text-3xl mb-3">
                    Add Owner to Filing
                </h2>
                <div className="grid mb-6">
                    <Input
                        placeholder="Search all team Applicants by name or FinCEN ID"
                        className="w-full input input-bordered input-secondary"
                        bordered={true}
                        onChange={handleAllApplicantSearchChange}
                    />
                </div>
                {/* Scrollable Container */}
                <div className="grid mb-6 max-h-96 overflow-y-auto border border-gray-300">
                    {filteredAllApplicants.map((applicant) => (
                        <div key={applicant.id} className="card w-full bg-base-100 shadow-md border border-gray-200">
                            <div className="card-body p-2">
                                <div className="flex justify-between items-center">
                                    <div>
                                        <p>{`${applicant?.first_name ?? ""} ${applicant?.last_name ?? ""}`.trim()} {applicant?.fincen_id ? `| ${applicant.fincen_id}` : ''}</p>
                                    </div>
                                    <div>
                                        <Button
                                            tag="a"
                                            href={route('filing.applicants.addToFiling', { id: filing.id, applicant_id: applicant.id })}
                                            color="primary"
                                            className="font-bold text-white"
                                        >
                                            Add to Filing
                                        </Button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    ))}
                </div>
                <div className="flex justify-between mt-8">
                    <Button
                        color="neutral"
                        onClick={() => setShowAddApplicantModal(false)}
                        className="font-bold text-white"
                    >
                        Cancel
                    </Button>
                    <Button
                        color="accent"
                        onClick={() => handleCreateApplicant()}
                        className="font-bold text-white"
                    >
                        Add Applicant Manually
                    </Button>
                    <Button
                        color="primary"
                        onClick={() => handleInviteApplicant()}
                        className="font-bold text-white"
                    >
                        Invite Applicant
                    </Button>
                </div>
            </div>
        </Modal>
    )
}